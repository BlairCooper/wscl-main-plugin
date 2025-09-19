<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:fo="http://www.w3.org/1999/XSL/Format">

	<xsl:import href="Common.xslt" />

	<xsl:output method="xml" />

	<xsl:attribute-set name="boldCentered">
		<xsl:attribute name="font-weight">bold</xsl:attribute>
		<xsl:attribute name="text-align">center</xsl:attribute>
		<xsl:attribute name="display-align">center</xsl:attribute>
		<xsl:attribute name="padding-top">8pt</xsl:attribute>
		<xsl:attribute name="padding-bottom">8pt</xsl:attribute>
	</xsl:attribute-set>

	<xsl:attribute-set name="fieldBox" use-attribute-sets="boldCentered">
		<xsl:attribute name="border-style">solid</xsl:attribute>
		<xsl:attribute name="border-width">thin</xsl:attribute>
		<xsl:attribute name="border-color">black</xsl:attribute>
		<xsl:attribute name="padding-top">6pt</xsl:attribute>
		<xsl:attribute name="padding-bottom">6pt</xsl:attribute>
 		<xsl:attribute name="font-size">8pt</xsl:attribute>
	</xsl:attribute-set>
	
	<xsl:attribute-set name="headerBox" use-attribute-sets="fieldBox">
		<xsl:attribute name="background-color">lightgray</xsl:attribute>
	</xsl:attribute-set>
	
	<xsl:attribute-set name="redTextHeaderBox" use-attribute-sets="headerBox">
		<xsl:attribute name="color">red</xsl:attribute>
	</xsl:attribute-set>

	<xsl:template name="BoldFieldBox">
		<xsl:param name="text" />

		<fo:table-cell xsl:use-attribute-sets="boldFieldBox" padding="10pt">
			<fo:block>
				<xsl:value-of select="$text" />
			</fo:block>
		</fo:table-cell>
	</xsl:template>
	
	<xsl:template match="/">
		<fo:root>
			<fo:layout-master-set>
				<fo:simple-page-master
					master-name="landscape"
					page-width="11in"
					page-height="8.5in"
					margin-top="12mm"
					margin-bottom="5mm"
					margin-left="12mm"
					margin-right="12mm" >
					<fo:region-body
						margin-top="10mm"
						margin-bottom="10mm"
						column-count="2"
						column-gap="4mm" />
					<fo:region-before extent="12mm" />
					<fo:region-after extent="5mm" />
				</fo:simple-page-master>
				<fo:simple-page-master
					master-name="portrait"
					page-width="8.5in"
					page-height="11in"
					margin-top="12mm"
					margin-bottom="5mm"
					margin-left="15mm"
					margin-right="15mm" >
					<fo:region-body
						margin-top="10mm"
						margin-bottom="10mm"
						column-count="2"
						column-gap="8mm" />
					<fo:region-before extent="12mm" />
					<fo:region-after extent="5mm" />
				</fo:simple-page-master>
			</fo:layout-master-set>

			<fo:page-sequence master-reference="portrait">
				<fo:static-content flow-name="xsl-region-before">
					<xsl:apply-templates select="/StagingSummary/ReportHeader" />
				</fo:static-content>

				<fo:static-content flow-name="xsl-region-after" xsl:use-attribute-sets="base">
					<fo:block display-align="center">
						<fo:table table-layout="fixed" width="100%">
							<fo:table-body>
								<fo:table-row>
									<fo:table-cell text-align="left" >
										<fo:block font-size="6pt">
											<xsl:apply-templates select="/StagingSummary/Timestamp" />
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
						<xsl:apply-templates select="/StagingSummary/Races" />
					</fo:block>
				</fo:flow>
			</fo:page-sequence>
		</fo:root>
	</xsl:template>

	<xsl:template match="ReportHeader">
		<fo:block>
			<fo:table table-layout="fixed" width="50mm" xsl:use-attribute-sets="base">
				<fo:table-column column-width="30mm" />
				<fo:table-column column-width="250mm" />
				<fo:table-body>
					<fo:table-row>
						<fo:table-cell>
							<fo:block>
								<xsl:value-of select="EventDate" />
							</fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block>
								<xsl:value-of select="EventName" />
							</fo:block>
						</fo:table-cell>
					</fo:table-row>
				</fo:table-body>
			</fo:table>
		</fo:block>
	</xsl:template>

	<xsl:template match="Races" >
		<fo:table table-layout="fixed" width="100%">
			<fo:table-body>
				<!-- Races -->
				<xsl:apply-templates />
				<!-- Event Summary -->
				<fo:table-row>
					<fo:table-cell>
						<fo:block>
							<fo:table table-layout="fixed" width="50%" >
								<fo:table-column column-width="25mm" />
								<fo:table-column column-width="40mm" />
								<fo:table-column column-width="14mm" />

								<fo:table-body>
									<fo:table-row>
										<fo:table-cell xsl:use-attribute-sets="boldCentered" border-start-style="solid" border-before-style="solid" border-after-style="solid">
											<fo:block>
												<xsl:text>TOTAL</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-before-style="solid" border-after-style="solid" >
											<fo:block>
												<fo:leader />
											</fo:block>
										</fo:table-cell>
										<fo:table-cell xsl:use-attribute-sets="boldCentered" border-end-style="solid" border-before-style="solid" border-after-style="solid">
											<fo:block>
												<xsl:value-of select="TotalRiders" />
											</fo:block>
										</fo:table-cell>
									</fo:table-row>
								</fo:table-body>
							</fo:table>
						</fo:block>
					</fo:table-cell>
				</fo:table-row>
				<fo:table-row>
					<fo:table-cell padding-top="10pt">
						<fo:block>
							<fo:leader />
						</fo:block>
					</fo:table-cell>
				</fo:table-row>
				<fo:table-row>
					<fo:table-cell>
						<fo:block>
							<fo:table table-layout="fixed" width="50%" >
								<fo:table-column column-width="25mm" />
								<fo:table-column column-width="40mm" />
								<fo:table-column column-width="14mm" />

								<fo:table-body>
									<fo:table-row>
										<fo:table-cell xsl:use-attribute-sets="boldCentered" border-start-style="solid" border-before-style="solid" border-after-style="solid">
											<fo:block>
												<xsl:text>ROWS WIDE</xsl:text>
											</fo:block>
										</fo:table-cell>
										<fo:table-cell border-before-style="solid" border-after-style="solid" >
											<fo:block>
												 <fo:leader />
											</fo:block>
										</fo:table-cell>
										<fo:table-cell xsl:use-attribute-sets="boldCentered" border-end-style="solid" border-before-style="solid" border-after-style="solid">
											<fo:block>
												<xsl:value-of select="RowWidth" />
											</fo:block>
										</fo:table-cell>
									</fo:table-row>
								</fo:table-body>
							</fo:table>
						</fo:block>
					</fo:table-cell>
				</fo:table-row>
			</fo:table-body>
		</fo:table>
	</xsl:template>

	<xsl:template match="Race">
		<fo:table-row>
			<fo:table-cell>
				<fo:block keep-together.within-column="always" >
	  				<fo:table table-layout="fixed" width="100%">
						<fo:table-body>
							<fo:table-row>
								<fo:table-cell>
									<fo:block font-weight="bold">
										<xsl:value-of select="StartTime" />
										<xsl:text> RACE</xsl:text>
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row>
								<fo:table-cell>
									<fo:block>
										 <fo:leader />
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row>
								<fo:table-cell>
									<fo:block>
										<xsl:apply-templates select="Categories" />
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row>
								<fo:table-cell>
									<fo:block>
										<fo:table table-layout="fixed" width="100%">
											<fo:table-column column-width="12mm" />
											<fo:table-column column-width="40mm" />
											<fo:table-column column-width="12mm" />
											<fo:table-column column-width="15mm" />
		
											<fo:table-body>
												<fo:table-row>
													<fo:table-cell xsl:use-attribute-sets="boldCentered">
														<fo:block>
															<xsl:text>TOTAL</xsl:text>
														</fo:block>
													</fo:table-cell>
													<fo:table-cell>
														<fo:block>
															 <fo:leader />
														</fo:block>
													</fo:table-cell>
													<fo:table-cell xsl:use-attribute-sets="boldCentered">
														<fo:block>
															<xsl:value-of select="Size" />
														</fo:block>
													</fo:table-cell>
													<fo:table-cell xsl:use-attribute-sets="boldCentered">
														<fo:block>
															<xsl:value-of select="Rows" />
														</fo:block>
													</fo:table-cell>
												</fo:table-row>
											</fo:table-body>
										</fo:table>
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block>
				<fo:block>
					 <fo:leader />
				</fo:block>
				<fo:block>
					 <fo:leader />
				</fo:block>
			</fo:table-cell> 
		</fo:table-row>
	</xsl:template>

	<xsl:template match="Categories" >
		<xsl:if test="*">
			<fo:table table-layout="fixed" width="100%">
				<fo:table-column column-width="12mm" />
				<fo:table-column column-width="40mm" />
				<fo:table-column column-width="12mm" />
				<fo:table-column column-width="15mm" />
	
				<fo:table-header>
					<fo:table-row>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								WAVE
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								CATEGORY
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								FIELD SIZE
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								ROW
							</xsl:with-param>
						</xsl:call-template>
					</fo:table-row>
				</fo:table-header>
	
				<fo:table-body>
					<xsl:apply-templates />
				</fo:table-body>
			</fo:table>
		</xsl:if>
	</xsl:template>

	<xsl:template match="Category">
		<fo:table-row>
			<fo:table-cell xsl:use-attribute-sets="fieldBox">
				<fo:block>
					<xsl:value-of select="Wave" />
				</fo:block>
			</fo:table-cell>
			<fo:table-cell xsl:use-attribute-sets="fieldBox">
				<fo:block>
					<xsl:value-of select="Name" />
				</fo:block>
			</fo:table-cell>
			<fo:table-cell xsl:use-attribute-sets="fieldBox">
				<fo:block>
					<xsl:value-of select="Size" />
				</fo:block>
			</fo:table-cell>
			<fo:table-cell xsl:use-attribute-sets="redTextHeaderBox" >
				<fo:block>
					<xsl:value-of select="FirstRow" />
					<xsl:text> - </xsl:text>
					<xsl:value-of select="LastRow" />
				</fo:block>
			</fo:table-cell>
		</fo:table-row>
	</xsl:template>

</xsl:stylesheet>
