<?xml version='1.0'?>
<!-- Docbook XML Style sheet the for PHPlot Reference Manual -->
<xsl:stylesheet version='1.0'
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:fo="http://www.w3.org/1999/XSL/Format"
                xmlns:xlink='http://www.w3.org/1999/xlink'
                xmlns:doc='http://docbook.org/ns/docbook'
                exclude-result-prefixes="xlink">

<!-- Load the DocBook XML Style sheet for FO (Format Objects).
     These files are generally downloaded via task `setup_tools`
-->
<xsl:import href="../../docbook-xsl/fo/docbook.xsl"/>

<!-- ===== FOP-specific parameters ===== -->
<!-- Using FOP >= 0.90, this also enables the PDF bookmark sidebar. -->
<xsl:param name="fop1.extensions" select="1"></xsl:param>

<!-- Without this, it fetches draft.png from sourceforge.net every time! -->
<xsl:param name="draft.mode">no</xsl:param>

<xsl:param name="paper.type">A4</xsl:param>

<!-- Default is to have a 4pc left indent, for some reason. Turn it off. -->
<xsl:param name="body.start.indent">0pt</xsl:param>
<!-- And then indent the abstract. (By default, abstract is flush left
     and body is indented - very odd.
-->
<xsl:attribute-set name="abstract.properties">
  <xsl:attribute name="start-indent">0.5in</xsl:attribute>
  <xsl:attribute name="end-indent">0.5in</xsl:attribute>
</xsl:attribute-set>

<!-- Turn off hyphenation because it doesn't work and reports a SEVERE error. -->
<xsl:param name="hyphenate">false</xsl:param>

<xsl:param name="highlight.source">1</xsl:param>
<xsl:param name="highlight.default.language">php</xsl:param>
<!-- Shade program listings. (HTML uses stylesheets, but PDF can't.) -->
<xsl:param name="shade.verbatim" select="1"></xsl:param>
<xsl:attribute-set name="shade.verbatim.style">
    <xsl:attribute name="background-color">#E0E0E0</xsl:attribute>
</xsl:attribute-set>

<!-- Decrease font size and add some padding in programlistings -->
<xsl:attribute-set name="monospace.verbatim.properties"
     use-attribute-sets="verbatim.properties monospace.properties">
  <xsl:attribute name="text-align">start</xsl:attribute>
  <xsl:attribute name="wrap-option">no-wrap</xsl:attribute>
  <xsl:attribute name="font-size">9pt</xsl:attribute>
  <xsl:attribute name="padding-top">0.5em</xsl:attribute>
  <xsl:attribute name="padding-bottom">0.5em</xsl:attribute>
  <xsl:attribute name="padding-left">0.5em</xsl:attribute>
</xsl:attribute-set>

<!-- By default there is no visual clue about an internal xref link.
     Underline and change their color.
-->
<xsl:attribute-set name="xref.properties">
  <xsl:attribute name="text-decoration">underline</xsl:attribute>
  <xsl:attribute name="color">blue</xsl:attribute>
</xsl:attribute-set>

<!-- Improve table borders -->
<xsl:param name="table.cell.border.thickness">1.0pt</xsl:param>
<xsl:param name="table.frame.border.thickness">1.5pt</xsl:param>
<xsl:param name="default.table.frame">all</xsl:param>

<!-- Suppress warnings on certain fonts -->
<xsl:param name="symbol.font.family"></xsl:param>

<!-- Display variablelists as "blocks", so the long defs flow better. -->
<xsl:param name="variablelist.as.blocks" select="1"></xsl:param>

<!-- Force a page break. I hate using this, and would prefer a 'need'
     (which doesn't work with Apache FOP), or a general 'keep with next'
     (which does not seem available). For those cases where dbfo keep-together
     isn't helpful, there is no choice but to force a page break.
-->
<xsl:template match="doc:pagebreak">
  <fo:block break-before="page"></fo:block>
</xsl:template>

<!-- This makes FO more like HTML, where the author affiliation/jobtitle are
     displayed in the title page. By default FO ignores that.
     It also sets apart the 'affiliation' from the name by unbolding it and
     making it italic.
-->
<xsl:template match="author" mode="titlepage.mode">
  <fo:block>
    <xsl:call-template name="anchor"/>
    <xsl:call-template name="person.name"/>
    <fo:block font-weight="normal" font-style="italic">
      <xsl:apply-templates select="affiliation" mode="titlepage.mode"/>
    </fo:block>
  </fo:block>
</xsl:template>

<!-- Customize page header so title is even on the first page of chapters
    and Reference Entries (which are almost all only a single page anyway).
    Otherwise, there are too many pages without headers.
-->
<xsl:template name="header.content">
  <xsl:param name="pageclass" select="''"/>
  <xsl:param name="sequence" select="''"/>
  <xsl:param name="position" select="''"/>
  <xsl:param name="gentext-key" select="''"/>
  <fo:block>
    <xsl:choose>
      <xsl:when test="$sequence='blank' or $pageclass='titlepage' or $pageclass='front'">
        <!-- nothing -->
      </xsl:when>
      <xsl:when test="$position='center'">
        <xsl:apply-templates select="." mode="titleabbrev.markup"/>
      </xsl:when>
    </xsl:choose>
  </fo:block>
</xsl:template>

<!-- ===== XHTML and PDF common parameters ===== -->

<!-- Number sections, and include parent number -->
<xsl:param name="section.autolabel" select="'1'" />
<xsl:param name="section.label.includes.component.label" select="'1'" />

<!-- Other style controls -->
<xsl:param name="callout.graphics" select="'0'" />
<xsl:param name="generate.index" select="0" />
<xsl:param name="refentry.xref.manvolnum" select="0" />

<xsl:param name="refentry.generate.name" select="0" />
<xsl:param name="refentry.generate.title" select="1" />

<!-- TOC controls
  Not all of these are used. We want a book TOC and reference TOC, but
  no chapter or section TOCs.
-->
<xsl:param name="generate.toc">
appendix  toc,title
book      toc,title
chapter   nop
part      nop
component toc
preface   toc,title
reference toc,title
sect1     nop
sect2     nop
sect3     nop
sect4     nop
sect5     nop
section   toc
division  toc
</xsl:param>
<!-- Include sect1 in the TOC, but not sect2 -->
<xsl:param name="toc.section.depth" select="1" />

</xsl:stylesheet>
