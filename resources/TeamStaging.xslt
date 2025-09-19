<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:fo="http://www.w3.org/1999/XSL/Format">

	<xsl:import href="Common.xslt" />

	<xsl:output method="xml" />
	
	<xsl:attribute-set name="riderCell">
		<xsl:attribute name="padding-top">2pt</xsl:attribute>
		<xsl:attribute name="padding-bottom">2pt</xsl:attribute>
		<xsl:attribute name="padding-left">5pt</xsl:attribute>
		<xsl:attribute name="font-size">10pt</xsl:attribute>
	</xsl:attribute-set>

	<xsl:template name="RiderCell">
		<xsl:param name="text" />
		<xsl:param name="align" select="'left'" />
		
		<fo:table-cell xsl:use-attribute-sets="riderCell">
			<xsl:attribute name="text-align"><xsl:value-of select="$align" /></xsl:attribute>
			<fo:block>
				<xsl:value-of select="$text" />
			</fo:block>
		</fo:table-cell>
	</xsl:template>
	
	<xsl:template match="/">
		<fo:root>
			<fo:layout-master-set>
				<fo:simple-page-master
					master-name="portrait"
					page-width="8.5in"
					page-height="11in"
					margin-top="12mm"
					margin-bottom="5mm"
					margin-left="15mm"
					margin-right="12mm" >
					<fo:region-body
						margin-top="30mm"
						margin-bottom="7mm" />
					<fo:region-before
						extent="30mm" />
					<fo:region-after
						extent="5mm" />
				</fo:simple-page-master>
			</fo:layout-master-set>

			<xsl:apply-templates />
		</fo:root>
	</xsl:template>

	<xsl:template match="Team">
		<fo:page-sequence master-reference="portrait">
			<!-- Header -->
			<fo:static-content flow-name="xsl-region-before">
				<xsl:call-template name="Headers">
					<xsl:with-param name="eventName">
						<xsl:value-of select="/TeamStaging/@eventName" />
					</xsl:with-param>
					<xsl:with-param name="teamName">
						<xsl:value-of select="@name" />
					</xsl:with-param>
				</xsl:call-template>
			</fo:static-content>
			
			<fo:static-content flow-name="xsl-region-after" xsl:use-attribute-sets="base">
				<fo:block display-align="center">
					<fo:table table-layout="fixed" width="100%">
						<fo:table-body>
							<fo:table-row>
								<fo:table-cell text-align="left" >
									<fo:block font-size="6pt">
										<xsl:value-of select="/TeamStaging/@timestamp" />
									</fo:block>
								</fo:table-cell>
								<fo:table-cell text-align="right" >
									<fo:block font-size="10pt">
										<xsl:value-of select="/TeamStaging/@eventDate" />
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block>
			</fo:static-content>
			
			<fo:flow flow-name="xsl-region-body" xsl:use-attribute-sets="base">
				<xsl:apply-templates />
			</fo:flow>
		</fo:page-sequence>
	</xsl:template>

	<xsl:template name="Headers">
		<xsl:param name="eventName" />
		<xsl:param name="teamName" />
		
		<fo:block>
			<fo:table table-layout="fixed" width="100%" border-spacing="0pt">
				<fo:table-body>
					<fo:table-row>
						<fo:table-cell>
							<xsl:call-template name="PageHeader">
								<xsl:with-param name="eventName">
									<xsl:value-of select="/TeamStaging/@eventName" />
								</xsl:with-param>
								<xsl:with-param name="teamName">
									<xsl:value-of select="@name" />
								</xsl:with-param>
							</xsl:call-template>
						</fo:table-cell>
					</fo:table-row>
					<fo:table-row>
						<fo:table-cell>
							<xsl:call-template name="ColumnHeader" />
						</fo:table-cell>
					</fo:table-row>
				</fo:table-body>
			</fo:table>
		</fo:block>
	</xsl:template>

	<xsl:template name="PageHeader">
		<xsl:param name="eventName" />
		<xsl:param name="teamName" />

		<fo:block>
			<fo:table table-layout="fixed" width="100%" border-spacing="0pt">
				<fo:table-column column-width="90%" />
				<fo:table-column column-width="10%" />
				<fo:table-body>
					<fo:table-row>
						<fo:table-cell>
							<fo:block>
								<fo:table table-layout="fixed" width="100%" border-spacing="0pt">
									<fo:table-body>
										<fo:table-row>
											<fo:table-cell number-columns-spanned="4">
												<fo:block font-size="24pt">
													<fo:inline text-decoration="underline">
														<xsl:value-of select="$eventName" />
													</fo:inline>
												</fo:block>
											</fo:table-cell>
										</fo:table-row>
										<fo:table-row>
											<fo:table-cell number-columns-spanned="4">
												<fo:block font-weight="bold" font-size="16pt">
													<xsl:value-of select="$teamName" />
													<xsl:text> - Staging</xsl:text>
												</fo:block>
											</fo:table-cell>
										</fo:table-row>
									</fo:table-body>
								</fo:table>
							</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block>
								<fo:external-graphic
									src="url('WSCL_Logo.png')"
									width="100%"
									content-width="scale-to-fit"
									content-height="scale-to-fit"
								/>
							</fo:block>
						</fo:table-cell>
					</fo:table-row>
				</fo:table-body>
			</fo:table>
		</fo:block>
	</xsl:template>

	<xsl:template name="ColumnHeader">
		<fo:block>
			<fo:table table-layout="fixed" width="100%">
				<fo:table-column column-width="20%" />
				<fo:table-column column-width="10%" />
				<fo:table-column column-width="35%" />
				<fo:table-column column-width="25%" />
				<fo:table-column column-width="10%" />
				<fo:table-body>
					<fo:table-row>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">Start Time</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">Bib</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">Name</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">Category</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">Row</xsl:with-param>
						</xsl:call-template>
					</fo:table-row>
				</fo:table-body>
			</fo:table>
		</fo:block>
	</xsl:template>

	<xsl:template match="Race">
		<fo:table>
			<fo:table-body table-layout="fixed" width="100%">
				<fo:table-row keep-together.within-page="always">
					<fo:table-cell>
						<fo:block
							padding-before="2pt"
							padding-after="0"
							font-weight="bold"
							font-size="12pt"
							>
							<xsl:value-of select="@time" />
						</fo:block>
						<fo:table table-layout="fixed" width="100%">
							<fo:table-column column-width="20%" />
							<fo:table-column column-width="10%" />
							<fo:table-column column-width="35%" />
							<fo:table-column column-width="25%" />
							<fo:table-column column-width="10%" />
							<fo:table-body>
								<xsl:apply-templates />
							</fo:table-body>
						</fo:table>
					</fo:table-cell>
				</fo:table-row>
			</fo:table-body>
		</fo:table>
	</xsl:template>
	
	<xsl:template match="Rider">
		<fo:table-row keep-with-next="auto">
			<xsl:attribute name="background-color">
				<xsl:choose>
					<xsl:when test="(position() mod 2) = 1">#E5E4E2</xsl:when>
					<xsl:otherwise>#FFFFFF</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			<xsl:call-template name="RiderCell">
				<xsl:with-param name="text">&#x00A0;</xsl:with-param>
				<xsl:with-param name="align">center</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="RiderCell">
				<xsl:with-param name="text">
					<xsl:value-of select="Bib" />
				</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="RiderCell">
				<xsl:with-param name="text">
					<xsl:value-of select="Name" />
				</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="RiderCell">
				<xsl:with-param name="text">
					<xsl:value-of select="Category" />
				</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="RiderCell">
				<xsl:with-param name="text">
					<xsl:value-of select="Row" />
				</xsl:with-param>
				<xsl:with-param name="align">center</xsl:with-param>
			</xsl:call-template>
		</fo:table-row>
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
		<fo:table-row  keep-together.within-page="always">
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
						<fo:table-column column-width="15mm" /> <!-- Points -->
						<fo:table-body>
							<xsl:apply-templates />
						</fo:table-body>
					</fo:table>
				</fo:block>
			</fo:table-cell>
		</fo:table-row>
	</xsl:template>


</xsl:stylesheet>
