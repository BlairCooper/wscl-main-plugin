<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:fo="http://www.w3.org/1999/XSL/Format">

	<xsl:output method="xml" />

	<xsl:include href="Common.xslt" />
	
	<xsl:template match="/">
		<fo:root>
			<fo:layout-master-set>
				<fo:simple-page-master margin-right="0.5in"
					margin-left="0.5in" margin-bottom="0.2in" margin-top="0.2in"
					page-width="8.5in" page-height="11in" master-name="portrait">
					<fo:region-body margin-top="0.3in" margin-bottom="0.4in" />
					<fo:region-before extent="0.3in" />
					<fo:region-after extent="0.3in" />
				</fo:simple-page-master>
			</fo:layout-master-set>

			<fo:page-sequence master-reference="portrait">
				<fo:static-content flow-name="xsl-region-before">
					<xsl:apply-templates select="/StagingOrder/ReportHeader" />
				</fo:static-content>

				<fo:static-content flow-name="xsl-region-after" xsl:use-attribute-sets="base">
					<fo:block display-align="center">
						<fo:table table-layout="fixed" width="100%">
							<fo:table-body>
								<fo:table-row>
									<fo:table-cell text-align="left" >
										<fo:block font-size="6pt">
											<xsl:apply-templates select="/StagingOrder/Timestamp" />
										</fo:block>
									</fo:table-cell>
									<fo:table-cell text-align="right" >
										<fo:block>
											PAGE
											<fo:page-number />
											of
											<fo:page-number-citation ref-id="last-page" />
										</fo:block>
									</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
					</fo:block>
				</fo:static-content>

				<fo:flow flow-name="xsl-region-body"
					xsl:use-attribute-sets="base">
					<fo:block>
						<xsl:apply-templates select="/StagingOrder/Riders" />
					</fo:block>
					<fo:block id="last-page" />
				</fo:flow>
			</fo:page-sequence>
		</fo:root>
	</xsl:template>

	<xsl:template match="ReportHeader">
		<fo:block>
			<fo:table table-layout="fixed" width="100%" xsl:use-attribute-sets="base">
				<fo:table-body>
					<fo:table-row>
						<fo:table-cell>
							<fo:block>
								<xsl:value-of select="EventName" />
								<fo:block/>
								<xsl:value-of select="EventDate" />
							</fo:block>
						</fo:table-cell>
						<fo:table-cell text-align="center">
							<fo:block>
								<xsl:text>By </xsl:text><xsl:value-of select="SortOrder" />
							</fo:block>
						</fo:table-cell>
						<fo:table-cell text-align="right">
							<fo:block>
								<xsl:text>STAGING ORDER</xsl:text>
							</fo:block>
						</fo:table-cell>
					</fo:table-row>
				</fo:table-body>
			</fo:table>
		</fo:block>
	</xsl:template>

	<xsl:template match="Riders">
		<fo:block padding-before="2pt" border-collapse="collapse" font-size="6pt">
			<fo:table table-layout="fixed" width="181mm"
				border-spacing="0pt">
				<fo:table-column column-width="10mm" />
				<fo:table-column column-width="20mm" />
				<fo:table-column column-width="20mm" />
				<fo:table-column column-width="10mm" />
				<fo:table-column column-width="10mm" />
				<fo:table-column column-width="50mm" />
				<fo:table-column column-width="32mm" />
				<fo:table-column column-width="10mm" />
				<fo:table-column column-width="10mm" />
				<fo:table-column column-width="10mm" />

				<fo:table-header>
					<fo:table-row>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Bib
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								First Name
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Last Name
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Grade
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Gender
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Team
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Category
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Wave
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Staging Row
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Points
							</xsl:with-param>
						</xsl:call-template>
					</fo:table-row>
				</fo:table-header>

				<fo:table-body>
					<xsl:apply-templates />
				</fo:table-body>
			</fo:table>
		</fo:block>
	</xsl:template>


	<xsl:template match="Rider">
		<fo:table-row keep-with-next="auto">
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
					<xsl:value-of select="Grade" />
				</xsl:with-param>
				<xsl:with-param name="align">center</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="FieldBox">
				<xsl:with-param name="text">
					<xsl:value-of select="Gender" />
				</xsl:with-param>
				<xsl:with-param name="align">center</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="FieldBox">
				<xsl:with-param name="text">
					<xsl:value-of select="Team" />
				</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="FieldBox">
				<xsl:with-param name="text">
					<xsl:value-of select="Category" />
				</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="FieldBox">
				<xsl:with-param name="text">
					<xsl:value-of select="Wave" />
				</xsl:with-param>
				<xsl:with-param name="align">center</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="FieldBox">
				<xsl:with-param name="text">
					<xsl:value-of select="StagingRow" />
				</xsl:with-param>
				<xsl:with-param name="align">center</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="FieldBox">
				<xsl:with-param name="text">
					<xsl:value-of select="Points" />
				</xsl:with-param>
				<xsl:with-param name="align">center</xsl:with-param>
			</xsl:call-template>
		</fo:table-row>
	</xsl:template>

</xsl:stylesheet>
