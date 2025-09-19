<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:fo="http://www.w3.org/1999/XSL/Format">

	<xsl:variable name="whiteRow" select='"#FFFFFF"' />
	<xsl:variable name="shadedRow" select='"#E5E4E2"' />
	<xsl:variable name="noBreakSpace" select='"&#x00A0;"' />

	<xsl:attribute-set name="base">
		<xsl:attribute name="font-size">8pt</xsl:attribute>
	</xsl:attribute-set>

	<xsl:attribute-set name="fieldBox">
		<xsl:attribute name="border-style">solid</xsl:attribute>
		<xsl:attribute name="border-width">thin</xsl:attribute>
		<xsl:attribute name="border-color">black</xsl:attribute>
		<xsl:attribute name="padding-top">3pt</xsl:attribute>
		<xsl:attribute name="padding-bottom">3pt</xsl:attribute>
		<xsl:attribute name="padding-left">3pt</xsl:attribute>
		<xsl:attribute name="display-align">center</xsl:attribute>
	</xsl:attribute-set>

	<xsl:attribute-set name="headerBox" use-attribute-sets="fieldBox">
		<xsl:attribute name="background-color">lightgray</xsl:attribute>
		<xsl:attribute name="display-align">center</xsl:attribute>
		<xsl:attribute name="text-align">center</xsl:attribute>
	</xsl:attribute-set>

	<xsl:attribute-set name="boldFieldBox" use-attribute-sets="fieldBox">
		<xsl:attribute name="text-align">center</xsl:attribute>
		<xsl:attribute name="font-weight">bold</xsl:attribute>
	</xsl:attribute-set>

	<xsl:template name="HeaderBox">
		<xsl:param name="text" />

		<fo:table-cell xsl:use-attribute-sets="headerBox">
			<fo:block>
				<xsl:value-of select="$text" />
			</fo:block>
		</fo:table-cell>
	</xsl:template>

	<xsl:template name="FieldBox">
		<xsl:param name="text" />
		<xsl:param name="align" select="'left'" />

		<fo:table-cell xsl:use-attribute-sets="fieldBox">
			<xsl:attribute name="text-align"><xsl:value-of select="$align" /></xsl:attribute>
			<fo:block>
				<xsl:value-of select="$text" />
			</fo:block>
		</fo:table-cell>
	</xsl:template>

	<xsl:template name="BoldFieldBox">
		<xsl:param name="text" />

		<fo:table-cell xsl:use-attribute-sets="boldFieldBox">
			<fo:block>
				<xsl:value-of select="$text" />
			</fo:block>
		</fo:table-cell>
	</xsl:template>

</xsl:stylesheet>
