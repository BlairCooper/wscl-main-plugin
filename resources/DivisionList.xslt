<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:fo="http://www.w3.org/1999/XSL/Format">

	<xsl:import href="Common.xslt" />

	<xsl:output method="xml" />

	<xsl:attribute-set name="boldCentered">
		<xsl:attribute name="font-weight">bold</xsl:attribute>
<!--		
		<xsl:attribute name="padding-top">8pt</xsl:attribute>
		<xsl:attribute name="padding-bottom">8pt</xsl:attribute>
-->
	</xsl:attribute-set>

	<xsl:attribute-set name="fieldBox" use-attribute-sets="boldCentered">
		<xsl:attribute name="border-style">none</xsl:attribute>
		<xsl:attribute name="border-width">thin</xsl:attribute>
		<xsl:attribute name="border-color">black</xsl:attribute>
		<xsl:attribute name="padding-top">2pt</xsl:attribute>
		<xsl:attribute name="padding-bottom">2pt</xsl:attribute>
		<xsl:attribute name="font-size">8pt</xsl:attribute>
	</xsl:attribute-set>
	
	<xsl:attribute-set name="headerBox" use-attribute-sets="fieldBox">
		<xsl:attribute name="border-style">solid</xsl:attribute>
		<xsl:attribute name="padding-top">6pt</xsl:attribute>
		<xsl:attribute name="padding-bottom">6pt</xsl:attribute>
		<xsl:attribute name="background-color">lightgray</xsl:attribute>
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
					margin-left="15mm"
					margin-right="15mm" >
					<fo:region-body
						margin-top="20mm"
						margin-bottom="10mm"
						column-count="2"
						column-gap="8mm" />
					<fo:region-before extent="20mm" />
					<fo:region-after extent="5mm" />
				</fo:simple-page-master>
			</fo:layout-master-set>

			<fo:page-sequence master-reference="portrait">
				<fo:static-content flow-name="xsl-region-before">
					<xsl:apply-templates select="/DivisionList/ReportHeader" />
				</fo:static-content>

				<fo:static-content flow-name="xsl-region-after" xsl:use-attribute-sets="base">
					<fo:block display-align="center">
						<fo:table table-layout="fixed" width="100%">
							<fo:table-body>
								<fo:table-row>
									<fo:table-cell text-align="left" >
										<fo:block font-size="6pt">
											<xsl:apply-templates select="/DivisionList/Timestamp" />
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
						<xsl:apply-templates select="/DivisionList/Divisions" />
					</fo:block>
				</fo:flow>
			</fo:page-sequence>
		</fo:root>
	</xsl:template>

	<xsl:template match="ReportHeader">
		<fo:block>
			<fo:table table-layout="fixed" width="100%" xsl:use-attribute-sets="base">
				<fo:table-column column-width="30mm" />
				<fo:table-column column-width="180mm" />
				<fo:table-body>
					<fo:table-row>
						<fo:table-cell>
							<fo:block font-size="12pt">
								Divisions
							</fo:block>
						</fo:table-cell>
                        <fo:table-cell>
                            <fo:block>
                                <xsl:apply-templates select="/DivisionList/Stats" />
                            </fo:block>
                        </fo:table-cell>
					</fo:table-row>
				</fo:table-body>
			</fo:table>
		</fo:block>
	</xsl:template>

	<xsl:template match="Divisions" >
		<fo:table table-layout="fixed" width="100%">
			<fo:table-body>
				<!-- Levels -->
				<xsl:apply-templates />
			</fo:table-body>
		</fo:table>
	</xsl:template>
	
	<xsl:template match="Level">
		<fo:table-row>
			<fo:table-cell>
				<fo:block keep-together.within-column="always" >
					<fo:table table-layout="fixed" width="100%">
						<fo:table-column column-width="25mm" />
						<fo:table-column column-width="12mm" />
						<fo:table-column column-width="50mm" />
						
						<fo:table-header>
							<fo:table-row>
								<xsl:call-template name="HeaderBox">
									<xsl:with-param name="text">
										Level
									</xsl:with-param>
								</xsl:call-template>
								<xsl:call-template name="HeaderBox">
									<xsl:with-param name="text">
										Division
									</xsl:with-param>
								</xsl:call-template>
								<xsl:call-template name="HeaderBox">
									<xsl:with-param name="text">
										Team
									</xsl:with-param>
								</xsl:call-template>
							</fo:table-row>
						</fo:table-header>
						
						<fo:table-body>
							<fo:table-row>
								<fo:table-cell xsl:use-attribute-sets="fieldBox">
									<fo:block>
										<xsl:value-of select="@name" />
									</fo:block>
								</fo:table-cell>
								<fo:table-cell xsl:use-attribute-sets="fieldBox">
									<fo:block>
									</fo:block>
								</fo:table-cell>
								<fo:table-cell xsl:use-attribute-sets="fieldBox">
									<fo:block>
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<xsl:apply-templates />
						</fo:table-body>
					</fo:table>
				</fo:block>
				<fo:block>
					<fo:leader />
				</fo:block>
			</fo:table-cell> 
		</fo:table-row>
	</xsl:template>

	<xsl:template match="Division">
		<fo:table-row>
			<fo:table-cell xsl:use-attribute-sets="fieldBox">
				<fo:block>
				</fo:block>
			</fo:table-cell>
			<fo:table-cell xsl:use-attribute-sets="fieldBox">
				<fo:block>
					<xsl:value-of select="@name" />
				</fo:block>
			</fo:table-cell>
			<fo:table-cell xsl:use-attribute-sets="fieldBox">
				<fo:block>
				</fo:block>
			</fo:table-cell>
		</fo:table-row>
		<xsl:apply-templates />
	</xsl:template>
	
	<xsl:template match="Team">
		<fo:table-row>
			<fo:table-cell xsl:use-attribute-sets="fieldBox">
				<fo:block>
				</fo:block>
			</fo:table-cell>
			<fo:table-cell xsl:use-attribute-sets="fieldBox">
				<fo:block>
				</fo:block>
			</fo:table-cell>
			<fo:table-cell xsl:use-attribute-sets="fieldBox">
				<fo:block>
					<xsl:value-of select="@name" /> (<xsl:value-of select="@size" />)
				</fo:block>
			</fo:table-cell>
		</fo:table-row>
	</xsl:template>

    <xsl:template match="Stats" >
        <fo:table table-layout="fixed" width="100%">
            <fo:table-column column-width="25mm" />
            <fo:table-column column-width="50mm" />
        
            <fo:table-body>
                <!-- Levels -->
                <xsl:apply-templates />
            </fo:table-body>
        </fo:table>
    </xsl:template>

    <xsl:template match="LevelStats">
        <fo:table-row>
            <fo:table-cell xsl:use-attribute-sets="fieldBox">
                <fo:block>
                    <xsl:value-of select="@name" />
                </fo:block>
            </fo:table-cell>
            <fo:table-cell xsl:use-attribute-sets="fieldBox">
                <xsl:apply-templates />
            </fo:table-cell>
        </fo:table-row>
    </xsl:template>

    <xsl:template match="DivisionStats">
        <fo:block>
            <xsl:value-of select="@name" />: <xsl:value-of select="@min" /> to <xsl:value-of select="@max" /> riders
       </fo:block>
    </xsl:template>

</xsl:stylesheet>
