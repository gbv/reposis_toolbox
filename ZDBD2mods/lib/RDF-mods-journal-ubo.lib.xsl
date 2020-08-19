<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns:mods="http://www.loc.gov/mods/v3" exclude-result-prefixes="rdf">
  <xsl:template match="rdf:type" priority="2">
    <xsl:choose>
      <xsl:when test="@rdf:resource='http://purl.org/ontology/bibo/Periodical'">
        <mods:genre type="intern" >journal</mods:genre>
      </xsl:when>
      <xsl:when test="@rdf:resource='http://purl.org/ontology/bibo/Series'">
        <mods:genre type="intern" >series</mods:genre>
      </xsl:when>
      <xsl:otherwise>
        <xsl:message>
          Unknown rdf-Type:
          <xsl:value-of select="." />
        </xsl:message>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
</xsl:stylesheet>