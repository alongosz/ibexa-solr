FROM solr:${SOLR_VERSION:-8.11}

# Copy solr config from the version used by Ibexa
COPY --chown=solr:solr ./src/lib/Resources/config/solr/ /opt/solr/server/solr/configsets/ibexa

# Prepare config
RUN cp /opt/solr/server/solr/configsets/_default/conf/solrconfig.xml /opt/solr/server/solr/configsets/ibexa \
 && cp /opt/solr/server/solr/configsets/_default/conf/stopwords.txt /opt/solr/server/solr/configsets/ibexa \
 && cp /opt/solr/server/solr/configsets/_default/conf/synonyms.txt /opt/solr/server/solr/configsets/ibexa \
 && sed -i.bak '/<updateRequestProcessorChain name="add-unknown-fields-to-the-schema">/,/<\/updateRequestProcessorChain>/d' /opt/solr/server/solr/configsets/ibexa/solrconfig.xml \
 && sed -ie 's/${solr.autoSoftCommit.maxTime:-1}/${solr.autoSoftCommit.maxTime:20}/' /opt/solr/server/solr/configsets/ibexa/solrconfig.xml

# Make sure core is created on startup
CMD ["solr-create", "-c", "collection1", "-d", "/opt/solr/server/solr/configsets/ibexa"]
