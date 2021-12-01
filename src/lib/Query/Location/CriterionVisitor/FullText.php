<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace Ibexa\Solr\Query\Location\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Solr\Query\Content\CriterionVisitor\FullText as ContentFullText;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

/**
 * Visits the FullText criterion.
 */
class FullText extends ContentFullText
{
    /**
     * Map field value to a proper Solr representation.
     *
     * @param \Ibexa\Contracts\Solr\Query\CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        $condition = $this->escapeQuote(parent::visit($criterion, $subVisitor));

        return "{!child of='document_type_id:content' v='document_type_id:content AND {$condition}'}";
    }
}

class_alias(FullText::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Location\CriterionVisitor\FullText');
