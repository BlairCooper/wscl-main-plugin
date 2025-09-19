<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:fo="http://www.w3.org/1999/XSL/Format">

	<xsl:import href="Common.xslt" />

	<xsl:output method="xml" />
	
	<xsl:variable name="promoteColor" select='"#82E0AA"' />
	<xsl:variable name="relegateColor" select='"#FFF733"' />
	
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
					master-name="landscape"
					page-width="11in"
					page-height="8.5in"
					margin-top="12mm"
					margin-bottom="5mm"
					margin-left="12mm"
					margin-right="12mm" >
					<fo:region-body
						margin-top="55mm"
						margin-bottom="7mm" />
					<fo:region-before
						extent="55mm" />
					<fo:region-after
						extent="5mm" />
				</fo:simple-page-master>
			</fo:layout-master-set>

			<xsl:apply-templates />
		</fo:root>
	</xsl:template>

	<xsl:template match="Category">
		<fo:page-sequence master-reference="landscape">
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
										<xsl:value-of select="/RiderAnalysis/@Timestamp" />
									</fo:block>
								</fo:table-cell>
								<fo:table-cell text-align="right" >
									<fo:block font-size="10pt">
										<xsl:text>Event ID: </xsl:text>
										<xsl:value-of select="/RiderAnalysis/@EventId" />
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:block>
			</fo:static-content>
			
			<!-- Body -->
			<fo:flow flow-name="xsl-region-body" xsl:use-attribute-sets="base">
				<xsl:apply-templates select="Riders" />
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
													<xsl:value-of select="/RiderAnalysis/@EventName" /> - <xsl:value-of select="/RiderAnalysis/@EventDate" />  
												</fo:block>
											</fo:table-cell>
										</fo:table-row>
										<fo:table-row>
											<fo:table-cell text-align="center" number-columns-spanned="4">
												<fo:block font-size="12pt">
													<xsl:value-of select="CategoryName" /> 
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
										<fo:table-row>
											<fo:table-cell>
												<fo:block>
													Number of Laps 
												</fo:block>
											</fo:table-cell>
											<fo:table-cell >
												<fo:block>
													<xsl:value-of select="NumberOfLaps" /> 
												</fo:block>
											</fo:table-cell>
											<xsl:choose>
												<xsl:when test="CurCatPromotionCutoff != 0">
													<fo:table-cell >
														<fo:block>
															Cutoff for a move up (current category) 
														</fo:block>
													</fo:table-cell>
													<fo:table-cell >
														<fo:block>
															<xsl:value-of select="CurCatPromotionCutoff" />% 
														</fo:block>
													</fo:table-cell>
												</xsl:when>
												<xsl:otherwise>
													<fo:table-cell number-columns-spanned="2">
														<fo:block ><xsl:copy-of select="$noBreakSpace" /></fo:block>
													</fo:table-cell>
												</xsl:otherwise>
											</xsl:choose>
										</fo:table-row>
										<fo:table-row>
											<fo:table-cell >
												<fo:block>
													Average Lap (in seconds) 
												</fo:block>
											</fo:table-cell>
											<fo:table-cell >
												<fo:block>
													<xsl:value-of select="AvgLapTime" /> 
												</fo:block>
											</fo:table-cell>
											<xsl:choose>
												<xsl:when test="CurCatRelegationCutoff != 0">
													<fo:table-cell >
														<fo:block>
															Cutoff for a move down (current category) 
														</fo:block>
													</fo:table-cell>
													<fo:table-cell >
														<fo:block>
															<xsl:value-of select="CurCatRelegationCutoff" />% 
														</fo:block>
													</fo:table-cell>
												</xsl:when>
												<xsl:otherwise>
													<fo:table-cell number-columns-spanned="2">
														<fo:block ><xsl:copy-of select="$noBreakSpace" /></fo:block>
													</fo:table-cell>
												</xsl:otherwise>
											</xsl:choose>
										</fo:table-row>
										<fo:table-row>
											<fo:table-cell text-align="center" number-columns-spanned="4" font-size="4pt">
												<fo:block>
													<fo:leader />
												</fo:block>
											</fo:table-cell>
										</fo:table-row>
										<xsl:choose>
											<xsl:when test="CatAbovePromotionCutoff != 0">
												<fo:table-row>
													<fo:table-cell >
														<fo:block>
															<xsl:value-of select="CategoryAbove" /> Avg Lap Time 
														</fo:block>
													</fo:table-cell>
													<fo:table-cell >
														<fo:block>
															<xsl:value-of select="AvgLapAbove" /> 
														</fo:block>
													</fo:table-cell>
													<fo:table-cell >
														<fo:block>
															Cutoff for a move up to <xsl:value-of select="CategoryAbove" /> (Category Above)
														</fo:block>
													</fo:table-cell>
													<fo:table-cell >
														<fo:block>
															<xsl:value-of select="CatAbovePromotionCutoff" />%
														</fo:block>
													</fo:table-cell>
												</fo:table-row>
											</xsl:when>
										</xsl:choose>
										<xsl:choose>
											<xsl:when test="CatBelowRelegationCutoff != 0">
												<fo:table-row>
													<fo:table-cell >
														<fo:block>
															<xsl:value-of select="CategoryBelow" /> Avg Lap Time 
														</fo:block>
													</fo:table-cell>
													<fo:table-cell >
														<fo:block>
															<xsl:value-of select="AvgLapBelow" /> 
														</fo:block>
													</fo:table-cell>
													<fo:table-cell >
														<fo:block>
															Cutoff for a move down to <xsl:value-of select="CategoryBelow" /> (Category Below)
														</fo:block>
													</fo:table-cell>
													<fo:table-cell >
														<fo:block>
															<xsl:value-of select="CatBelowRelegationCutoff" />%
														</fo:block>
													</fo:table-cell>
												</fo:table-row>
											</xsl:when>
										</xsl:choose>
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
					<fo:table-row>
						<fo:table-cell
							number-columns-spanned="2"
							border-start-style="solid"
							border-end-style="solid"
							border-before-style="solid"
							border-after-style="solid"
							padding-top="2pt"
							padding-bottom="2pt"
							padding-left="5pt"
							>
							<fo:block>
								<fo:table>
									<fo:table-column column-width="20%" />
									<fo:table-column column-width="20%" />
									<fo:table-column column-width="60%" />
									
									<fo:table-body>
										<fo:table-row>
											<fo:table-cell>
												<fo:block>Legend:</fo:block>
												<fo:block>
													<fo:inline>
														<xsl:attribute name="background-color">
															<xsl:copy-of select="$promoteColor"/>
														</xsl:attribute>
														Candidate to move up a category
													</fo:inline>
												</fo:block>
												<fo:block>
													<fo:inline>
														<xsl:attribute name="background-color">
															<xsl:copy-of select="$relegateColor"/>
														</xsl:attribute>
														Candidate to move down a category
													</fo:inline>
												</fo:block>
											</fo:table-cell>
											<fo:table-cell>
												<fo:block>
													Gap To First Place (seconds):
												</fo:block>
												<fo:block>
													Percent off First Place:
												</fo:block>
												<fo:block>
													Percent off Category Above:
												</fo:block>
												<fo:block>
													Percent off Category Below:
												</fo:block>
											</fo:table-cell>
											<fo:table-cell>
												<fo:block>
													Number of seconds behind the first place rider
												</fo:block>
												<fo:block>
													Percent of time behind first place rider
												</fo:block>
												<fo:block>
													Avg lap relative to to the avg lap of the category above
												</fo:block>
												<fo:block>
													Avg lap relative to to the avg lap of the category below
												</fo:block>
											</fo:table-cell>
										</fo:table-row>
										<fo:table-row>
											<fo:table-cell number-columns-spanned="3">
												<fo:block>
													To be a candidate to move up a rider's average lap time must be
													either under the cutoff for their current category,
													OR under the cutoff for the category above.
												</fo:block>
												<fo:block>
													To be a candidate to move down a rider's average lap must be
													both over the cutoff for their current category,
													AND over the cutoff for the category below.
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
	
	<xsl:template match="Riders">
		<fo:block padding-before="2pt" border-collapse="collapse">
			<fo:table table-layout="fixed" width="100%" border-spacing="0pt">
				<fo:table-column column-width="10mm" />
				<fo:table-column column-width="10mm" />
				<fo:table-column column-width="10mm" />
				<fo:table-column column-width="60mm" />
				<fo:table-column column-width="12mm" />
				<fo:table-column column-width="20mm" />
				<fo:table-column column-width="20mm" />
				<fo:table-column column-width="25mm" />
				<fo:table-column column-width="20mm" />
				<fo:table-column column-width="15mm" />
				<fo:table-column column-width="17mm" />
				<fo:table-column column-width="17mm" />
				<fo:table-column column-width="17mm" />
				
				<fo:table-header>
					<fo:table-row>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Rank
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Bib
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Mand.
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Name
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Grade
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Fastest Lap
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Average Lap
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Average Lap (seconds)
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Race Time
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Gap To First Place (seconds)
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Percent off First Place
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Percent off Category Above
							</xsl:with-param>
						</xsl:call-template>
						<xsl:call-template name="HeaderBox">
							<xsl:with-param name="text">
								Percent off Category Below
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
			<xsl:attribute name="background-color">
				<xsl:choose>
					<xsl:when test="@Promote='Y'"><xsl:copy-of select="$promoteColor"/></xsl:when>
					<xsl:when test="@Relegate='Y'"><xsl:copy-of select="$relegateColor"/></xsl:when>
					<xsl:when test="(position() mod 2) = 1"><xsl:copy-of select="$shadedRow"/></xsl:when>
					<xsl:otherwise><xsl:copy-of select="$whiteRow"/></xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			<xsl:call-template name="RiderCell">
				<xsl:with-param name="text">
					<xsl:value-of select="Rank" />
				</xsl:with-param>
				<xsl:with-param name="align">center</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="RiderCell">
				<xsl:with-param name="text">
					<xsl:value-of select="Bib" />
				</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="RiderCell">
				<xsl:with-param name="text">
					<xsl:choose>
						<xsl:when test="@MandatoryUpgrade='Y'"> up</xsl:when>
						<xsl:when test="@MandatoryDowngrade='Y'"> dn</xsl:when>
						<xsl:otherwise></xsl:otherwise>
					</xsl:choose>
				</xsl:with-param>
			</xsl:call-template>
			<fo:table-cell xsl:use-attribute-sets="riderCell" text-align="left">
				<fo:block>
					<xsl:value-of select="Name" />
				</fo:block>
				<fo:block>
					<xsl:value-of select="Team" />
				</fo:block>
			</fo:table-cell>
			<xsl:call-template name="RiderCell">
				<xsl:with-param name="text">
					<xsl:value-of select="Grade" />
				</xsl:with-param>
				<xsl:with-param name="align">center</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="RiderCell">
				<xsl:with-param name="text">
					<xsl:value-of select="FastestLap" />
				</xsl:with-param>
				<xsl:with-param name="align">right</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="RiderCell">
				<xsl:with-param name="text">
					<xsl:value-of select="AverageLap" />
				</xsl:with-param>
				<xsl:with-param name="align">right</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="RiderCell">
				<xsl:with-param name="text">
					<xsl:value-of select="AverageLapSeconds" />
				</xsl:with-param>
				<xsl:with-param name="align">right</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="RiderCell">
				<xsl:with-param name="text">
					<xsl:value-of select="RaceTime" />
				</xsl:with-param>
				<xsl:with-param name="align">right</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="RiderCell">
				<xsl:with-param name="text">
					<xsl:choose>
						<xsl:when test="Rank='1'"></xsl:when>
						<xsl:otherwise><xsl:value-of select="GapToFirst" /></xsl:otherwise>
					</xsl:choose>
				</xsl:with-param>
				<xsl:with-param name="align">right</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="RiderCell">
				<xsl:with-param name="text">
					<xsl:choose>
						<xsl:when test="Rank='1'"></xsl:when>
						<xsl:otherwise><xsl:value-of select="PercentOfFirst" />%</xsl:otherwise>
					</xsl:choose>
				</xsl:with-param>
				<xsl:with-param name="align">right</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="RiderCell">
				<xsl:with-param name="text">
					<xsl:choose>
						<xsl:when test="PercentOfCatAbove='0.0'">n/a</xsl:when>
						<xsl:otherwise><xsl:value-of select="PercentOfCatAbove" />%</xsl:otherwise>
					</xsl:choose>
				</xsl:with-param>
				<xsl:with-param name="align">
					<xsl:choose>
						<xsl:when test="PercentOfCatAbove='0.0'">center</xsl:when>
						<xsl:otherwise>right</xsl:otherwise>
					</xsl:choose>
				</xsl:with-param>
			</xsl:call-template>
			<xsl:choose>
				<xsl:when test="PercentOfCatBelow='0.0'">
					<xsl:call-template name="RiderCell">
						<xsl:with-param name="text">n/a</xsl:with-param>
						<xsl:with-param name="align">center</xsl:with-param>
					</xsl:call-template>
				</xsl:when>
				<xsl:otherwise>
					<xsl:call-template name="RiderCell">
						<xsl:with-param name="text">
							<xsl:value-of select="PercentOfCatBelow" />%
						</xsl:with-param>
						<xsl:with-param name="align">right</xsl:with-param>
					</xsl:call-template>
				</xsl:otherwise>
			</xsl:choose>
		</fo:table-row>
	</xsl:template>

</xsl:stylesheet>
