<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr;

use Doctrine\DBAL\Connection;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Core\Search\Common\IncrementalIndexer;
use Ibexa\Contracts\Core\Persistence\Content\ContentInfo;
use Ibexa\Solr\Handler as SolrSearchHandler;
use Ibexa\Contracts\Core\Persistence\Handler as PersistenceHandler;
use Psr\Log\LoggerInterface;
use Exception;

class Indexer extends IncrementalIndexer
{
    /**
     * @var \Ibexa\Solr\Handler
     */
    protected $searchHandler;

    public function __construct(
        LoggerInterface $logger,
        PersistenceHandler $persistenceHandler,
        Connection $connection,
        SolrSearchHandler $searchHandler
    ) {
        parent::__construct($logger, $persistenceHandler, $connection, $searchHandler);
    }

    public function getName()
    {
        return 'eZ Platform Solr Search Engine';
    }

    public function purge()
    {
        $this->searchHandler->purgeIndex();
    }

    public function updateSearchIndex(array $contentIds, $commit)
    {
        $documents = [];
        $contentHandler = $this->persistenceHandler->contentHandler();
        foreach ($contentIds as $contentId) {
            try {
                $info = $contentHandler->loadContentInfo($contentId);
                if ($info->status === ContentInfo::STATUS_PUBLISHED) {
                    $content = $contentHandler->load($contentId, $info->currentVersionNo);
                    $documents[] = $this->searchHandler->generateDocument($content);
                } else {
                    $this->searchHandler->deleteContent($contentId);
                }
            } catch (NotFoundException $e) {
                $this->searchHandler->deleteContent($contentId);
            } catch (Exception $e) {
                $context = [
                    'contentId' => $contentId,
                    'error' => $e->getMessage(),
                ];
                $this->logger->error('Unable to index the content', $context);
            }
        }

        if (!empty($documents)) {
            $this->searchHandler->bulkIndexDocuments($documents);
        }

        if ($commit) {
            $this->searchHandler->commit(true);
        }
    }
}

class_alias(Indexer::class, 'EzSystems\EzPlatformSolrSearchEngine\Indexer');
