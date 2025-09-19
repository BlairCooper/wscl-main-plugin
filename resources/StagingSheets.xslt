<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:fo="http://www.w3.org/1999/XSL/Format">

	<xsl:import href="Common.xslt" />

	<xsl:output method="xml" />
	
	<xsl:template match="/">
		<fo:root>
			<fo:layout-master-set>
				<fo:simple-page-master
					master-name="portrait"
					page-width="8.5in"
					page-height="11in"
					margin-top="12mm"
					margin-bottom="5mm"
					margin-left="20mm"
					margin-right="12mm" >
					<fo:region-body
						margin-top="45mm"
						margin-bottom="7mm" />
					<fo:region-before
						extent="41mm" />
					<fo:region-after
						extent="5mm" />
				</fo:simple-page-master>
			</fo:layout-master-set>

			<xsl:apply-templates />
		</fo:root>
	</xsl:template>

	<xsl:template match="StagingSheet">
		<fo:page-sequence master-reference="portrait">
			<!-- Header -->
			<fo:static-content flow-name="xsl-region-before">
				<xsl:apply-templates select="./ReportHeader" />
			</fo:static-content>
			
			<fo:static-content flow-name="xsl-region-after" xsl:use-attribute-sets="base">
				<fo:block display-align="center">
					<fo:table table-layout="fixed" width="100%">
						<fo:table-body>
							<fo:table-row>
								<fo:table-cell text-align="left" >
									<fo:block font-size="6pt">
										<xsl:apply-templates select="./Timestamp" />
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block>
			</fo:static-content>
			
			<fo:flow flow-name="xsl-region-body" xsl:use-attribute-sets="base">
				<xsl:apply-templates select="./Rows" />
			</fo:flow>
		</fo:page-sequence>
	</xsl:template>

	<xsl:template match="ReportHeader">
		<fo:block>
			<fo:table table-layout="fixed" width="100%">
				<fo:table-column column-width="20%" />
				<fo:table-column column-width="20%" />
				<fo:table-column column-width="20%" />
				<fo:table-column column-width="30%" />
				<fo:table-column column-width="10%" />
				<fo:table-body>
					<fo:table-row>
						<fo:table-cell number-columns-spanned="2">
							<fo:block font-weight="bold" font-size="18pt">
								<xsl:value-of select="Category" />
							</fo:block>
						</fo:table-cell>
						<xsl:apply-templates select="Instructions" />
					</fo:table-row>
					<fo:table-row>
						<fo:table-cell>
							<fo:block font-size="9pt">
								<xsl:value-of select="EventDate" />
							</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block font-size="9pt">
								<xsl:value-of select="EventName" />
							</fo:block>
						</fo:table-cell>
					</fo:table-row>
					<fo:table-row>
						<xsl:choose>
							<xsl:when test="string-length(Wave) != 0" >
								<fo:table-cell>
									<fo:block font-size="12pt">
										<xsl:text>WAVE:</xsl:text>
									</fo:block>
								</fo:table-cell>
								<fo:table-cell>
									<fo:block font-weight="bold" font-size="12pt">
										<xsl:value-of select="Wave" />
									</fo:block>
								</fo:table-cell>
							</xsl:when>
							<xsl:otherwise>
								<fo:table-cell number-columns-spanned="2">
									<fo:block>&#x00A0;</fo:block>
								</fo:table-cell>
							</xsl:otherwise>
						</xsl:choose>
					</fo:table-row>
					<fo:table-row>
						<fo:table-cell>
							<fo:block font-size="12pt">
								<xsl:text>RACE:</xsl:text>
							</fo:block>
						</fo:table-cell>
						<fo:table-cell >
							<fo:block font-weight="bold" font-size="12pt">
								<xsl:value-of select="StartTime" />
							</fo:block>
						</fo:table-cell>
					</fo:table-row>
					<fo:table-row>
						<fo:table-cell>
							<fo:block font-size="12pt">
								<xsl:text>STAGING TIME:</xsl:text>
							</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block font-weight="bold" font-size="12pt">
								<xsl:value-of select="StagingTime" />
							</fo:block>
						</fo:table-cell>
					</fo:table-row>
					<fo:table-row>
						<fo:table-cell>
							<fo:block font-size="12pt">
								<xsl:text># RIDERS WIDE:</xsl:text>
							</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block font-weight="bold" font-size="12pt">
								<xsl:value-of select="RowWidth" />
							</fo:block>
						</fo:table-cell>
					</fo:table-row>
				</fo:table-body>
			</fo:table>
		</fo:block>
	</xsl:template>

	<xsl:template match="Instructions">
		<fo:table-cell number-columns-spanned="3"
					   number-rows-spanned="6"
					   border-style="solid"
					   border-width="medium"
					   border-color="black"
					   padding="3pt"
					   color="red">
			<fo:block font-size="10pt">
				<xsl:apply-templates />
			</fo:block>
		</fo:table-cell>
	</xsl:template>
	
	<xsl:template match="Instruction">
		<fo:block>
			<xsl:apply-templates />
		</fo:block>
	</xsl:template>
	
	<xsl:template match="Rows">
		<fo:block padding-before="2pt" border-collapse="collapse" font-size="10pt">
			<fo:table table-layout="fixed" width="100%"
				border-spacing="0pt">
				<fo:table-column column-width="14mm" /> <!-- Row -->
				<fo:table-column column-width="15mm" /> <!-- Order -->
				<fo:table-column column-width="15mm" /> <!-- Bib -->
				<fo:table-column column-width="25mm" /> <!-- First -->
				<fo:table-column column-width="35mm" /> <!-- Last -->
				<fo:table-column column-width="65mm" /> <!-- Team -->
				<fo:table-column column-width="15mm" /> <!-- Points -->

				<fo:table-header>
					<fo:table-row>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">ROW</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">ORDER</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">BIB</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">FIRST</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">LAST</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">TEAM</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">POINTS</xsl:with-param>
						</xsl:call-template>
					</fo:table-row>
				</fo:table-header>

				<fo:table-body>
					<xsl:apply-templates />
				</fo:table-body>
			</fo:table>
		</fo:block>
	</xsl:template>

	<xsl:template match="StagingRow">
		<fo:table-row keep-together.within-page="always">
			<fo:table-cell xsl:use-attribute-sets="boldFieldBox">
				<fo:block>
					<xsl:value-of select="@number" /> <!-- Row -->
				</fo:block>
			</fo:table-cell>
			<fo:table-cell number-columns-spanned="6" keep-with-next.within-page="always">
				<fo:block>
					<fo:table table-layout="fixed" width="100%" border-spacing="0pt">
						<fo:table-column column-width="15mm" /> <!-- Order -->
						<fo:table-column column-width="15mm" /> <!-- Bib -->
						<fo:table-column column-width="25mm" /> <!-- First -->
						<fo:table-column column-width="35mm" /> <!-- Last -->
						<fo:table-column column-width="65mm" /> <!-- Team -->
						<fo:table-column column-width="15mm" /> <!-- Score/Points -->
						<fo:table-body>
							<xsl:apply-templates />
						</fo:table-body>
					</fo:table>
				</fo:block>
			</fo:table-cell>
		</fo:table-row>
	</xsl:template>

	<xsl:template match="Rider">
		<fo:table-row>
			<xsl:call-template name="FieldBox">
				<xsl:with-param name="text">
					<xsl:value-of select="Order" />
				</xsl:with-param>
				<xsl:with-param name="align">center</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="FieldBox">
				<xsl:with-param name="text">
					<xsl:value-of select="Bib" />
				</xsl:with-param>
				<xsl:with-param name="align">center</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="FieldBox">
				<xsl:with-param name="text">
					<xsl:value-of select="Firstname" />
				</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="FieldBox">
				<xsl:with-param name="text">
					<xsl:value-of select="Lastname" />
				</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="FieldBox">
				<xsl:with-param name="text">
					<xsl:value-of select="Team" />
				</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="FieldBox">
				<xsl:with-param name="text">
 					<xsl:value-of select="StagingScore" />
 					<!--  / <xsl:value-of select="Points" /> --> 
				</xsl:with-param>
				<xsl:with-param name="align">center</xsl:with-param>
			</xsl:call-template>
		</fo:table-row>
	</xsl:template>

</xsl:stylesheet>
