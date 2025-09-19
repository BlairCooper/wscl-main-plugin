<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:fo="http://www.w3.org/1999/XSL/Format">

	<xsl:import href="Common.xslt" />

	<xsl:output method="xml" />
	
	<xsl:variable name="excludedColor" select='"#FFF733"' />
	
	<xsl:attribute-set name="riderCell">
		<xsl:attribute name="padding-top">2pt</xsl:attribute>
		<xsl:attribute name="padding-bottom">2pt</xsl:attribute>
		<xsl:attribute name="padding-left">5pt</xsl:attribute>
		<xsl:attribute name="padding-right">3pt</xsl:attribute>
		<xsl:attribute name="font-size">8pt</xsl:attribute>
	</xsl:attribute-set>

	<xsl:template match="/">
		<fo:root>
			<fo:layout-master-set>
				<fo:simple-page-master
					master-name="portrait"
					page-width="8.5in"
					page-height="11in"
					margin-top="12mm"
					margin-bottom="5mm"
					margin-left="12mm"
					margin-right="12mm" >
					<fo:region-body
						margin-top="25mm"
						margin-bottom="7mm" />
					<fo:region-before
						extent="25mm" />
					<fo:region-after
						extent="5mm" />
				</fo:simple-page-master>
			</fo:layout-master-set>

			<xsl:apply-templates />
		</fo:root>
	</xsl:template>

	<xsl:template match="HighSchool | MiddleSchool">
		<fo:page-sequence master-reference="portrait">
			<!-- Header -->
			<fo:static-content flow-name="xsl-region-before">
				<xsl:apply-templates select="ReportHeader" />
			</fo:static-content>

			<!-- Footer -->
			<fo:static-content flow-name="xsl-region-after" xsl:use-attribute-sets="base">
				<fo:block display-align="center">
					<fo:table table-layout="fixed" width="100%">
						<fo:table-body>
							<fo:table-row>
								<fo:table-cell text-align="left" >
									<fo:block font-size="6pt">
										<xsl:value-of select="/TeamScoringAnalysis/@Timestamp" />
									</fo:block>
								</fo:table-cell>
								<fo:table-cell text-align="right" >
									<fo:block font-size="10pt">
										<xsl:text>Event ID: </xsl:text>
										<xsl:value-of select="/TeamScoringAnalysis/@EventId" />
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block>
			</fo:static-content>
			
			<!-- Body -->
			<fo:flow flow-name="xsl-region-body" xsl:use-attribute-sets="base">
<!--				<xsl:apply-templates select="Teams" /> -->
				<xsl:apply-templates select="Division" />
			</fo:flow>
		</fo:page-sequence>
	</xsl:template>

	<xsl:template match="ReportHeader">
		<fo:block>
			<fo:table table-layout="fixed" width="100%" xsl:use-attribute-sets="base">
				<fo:table-column column-width="88%" />
				<fo:table-column column-width="12%" />

				<fo:table-body>
					<fo:table-row>
						<fo:table-cell font-size="9pt">
							<fo:block>
								<fo:table table-layout="fixed" width="100%" border-spacing="0pt">
									<fo:table-column column-width="40%" />
									<fo:table-column column-width="10%" />
									<fo:table-column column-width="40%" />
									<fo:table-column column-width="10%" />
								
									<fo:table-body>
										<fo:table-row>
											<fo:table-cell text-align="center" number-columns-spanned="4">
												<fo:block font-size="14pt">
													<xsl:value-of select="/TeamScoringAnalysis/@EventName" /> - <xsl:value-of select="/TeamScoringAnalysis/@EventDate" />  
												</fo:block>
											</fo:table-cell>
										</fo:table-row>
										<fo:table-row>
											<fo:table-cell text-align="center" number-columns-spanned="4">
												<fo:block font-size="12pt">
													<xsl:value-of select="../@Name" /> 
												</fo:block>
											</fo:table-cell>
										</fo:table-row>
										<fo:table-row>
											<fo:table-cell text-align="center" number-columns-spanned="4" font-size="4pt">
												<fo:block>
													<fo:leader />
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
	
	<xsl:template name="DivisionHeader">
		<fo:block padding-top="24pt">
			<fo:table table-layout="fixed" width="100%" xsl:use-attribute-sets="base">
				<fo:table-column column-width="100%" />
				
				<fo:table-body>
					<fo:table-row>
						<fo:table-cell text-align="center">
							<fo:block font-size="12pt">
								<xsl:value-of select="@Name" /> 
							</fo:block>
						</fo:table-cell>
					</fo:table-row>
				</fo:table-body>
			</fo:table>
		</fo:block>
	</xsl:template>

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
	
	<xsl:template match="Division">
		<xsl:call-template name="DivisionHeader" />
		<xsl:apply-templates select="Teams" />
	</xsl:template>

	<xsl:template match="Teams">
		<fo:block padding-before="2pt" border-collapse="collapse">
			<fo:table table-layout="fixed" width="100%" border-spacing="0pt">
				<fo:table-column column-width="10mm" />
				<fo:table-column column-width="45mm" />
				<fo:table-column column-width="15mm" />
				<fo:table-column column-width="15mm" />
				<fo:table-column column-width="15mm" />
				<fo:table-column column-width="15mm" />
				<fo:table-column column-width="20mm" />
				<fo:table-column column-width="20mm" />
				<fo:table-column column-width="25mm" />
				
				<fo:table-header>
					<fo:table-row>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Rank
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Team
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Points
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Riders
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Started
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Finished
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Gender (F/M)
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Categories
								<xsl:choose>
									<xsl:when test="../@HighSchool='Y'"><xsl:text>(B / I / J / V)</xsl:text></xsl:when>
									<xsl:otherwise><xsl:text>(6 / 7 / 8 / A)</xsl:text></xsl:otherwise>
								</xsl:choose>
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Category Size
								<xsl:choose>
									<xsl:when test="../@HighSchool='Y'"><xsl:text>(B / I / J / V)</xsl:text></xsl:when>
									<xsl:otherwise><xsl:text>(6 / 7 / 8 / A)</xsl:text></xsl:otherwise>
								</xsl:choose>
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
	
	<xsl:template match="Team">
		<fo:table-row keep-with-next="auto">
			<xsl:attribute name="background-color">
				<xsl:choose>
					<xsl:when test="@Excluded='Y'"><xsl:copy-of select="$excludedColor"/></xsl:when>
					<xsl:when test="(position() mod 2) = 1"><xsl:copy-of select="$shadedRow"/></xsl:when>
					<xsl:otherwise><xsl:copy-of select="$whiteRow"/></xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			<xsl:call-template name="RiderCell">
				<xsl:with-param name="text">
					<xsl:value-of select="Rank" />
				</xsl:with-param>
				<xsl:with-param name="align">right</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="RiderCell">
				<xsl:with-param name="text">
					<xsl:value-of select="@Name" />
				</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="RiderCell">
				<xsl:with-param name="text">
					<xsl:value-of select="Points" />
				</xsl:with-param>
				<xsl:with-param name="align">right</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="RiderCell">
				<xsl:with-param name="text">
					<xsl:value-of select="Riders" />
				</xsl:with-param>
				<xsl:with-param name="align">right</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="RiderCell">
				<xsl:with-param name="text">
					<xsl:value-of select="Started" />
				</xsl:with-param>
				<xsl:with-param name="align">right</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="RiderCell">
				<xsl:with-param name="text">
					<xsl:value-of select="Finished" />
				</xsl:with-param>
				<xsl:with-param name="align">right</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="RiderCell">
				<xsl:with-param name="text">
					<xsl:value-of select="Female" /> / <xsl:value-of select="Male" />
				</xsl:with-param>
				<xsl:with-param name="align">center</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="RiderCell">
				<xsl:with-param name="text">
					<xsl:value-of select="@Category_1" /> / 
					<xsl:value-of select="@Category_2" /> / 
					<xsl:value-of select="@Category_3" /> / 
					<xsl:value-of select="@Category_4" /> 
				</xsl:with-param>
				<xsl:with-param name="align">center</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="RiderCell">
				<xsl:with-param name="text">
					<xsl:value-of select="../../@Category_1" /> / 
					<xsl:value-of select="../../@Category_2" /> / 
					<xsl:value-of select="../../@Category_3" /> / 
					<xsl:value-of select="../../@Category_4" /> 
				</xsl:with-param>
				<xsl:with-param name="align">center</xsl:with-param>
			</xsl:call-template>
		</fo:table-row>
	</xsl:template>

</xsl:stylesheet>
