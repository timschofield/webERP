<?xml version='1.0'?>
<!-- Docbook XML Style sheet for the PHPlot Reference Manual -->
<xsl:stylesheet version='1.0'
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:doc='http://docbook.org/ns/docbook'
                xmlns="http://www.w3.org/1999/xhtml"
                exclude-result-prefixes="#default">

<!-- Load the DocBook XML Style sheet for separate (chunked) XHTML files.
     These files are generally downloaded via task `setup_tools`
-->
<xsl:import href="../../docbook-xsl/xhtml/chunk.xsl"/>

<!-- Was: Encoding now defaults to utf-8; change it back to "normal". -->
<!--<xsl:param name="chunker.output.encoding" select="'ISO-8859-1'" />-->

<!-- Use meaningful output filenames -->
<xsl:param name="use.id.as.filename" select="'1'" />

<!-- Reference the HTML stylesheet -->
<xsl:param name="html.stylesheet" select="'phplotdoc.css'" />

<!-- Number sections, and include parent number -->
<xsl:param name="section.autolabel" select="'1'" />
<xsl:param name="section.label.includes.component.label" select="'1'" />

<!-- Other style controls -->
<xsl:param name="callout.graphics" select="'0'" />
<xsl:param name="generate.index" select="0" />
    <!-- The default anyway for XHTML? -->
<xsl:param name="make.valid.html" select="1" />
<xsl:param name="refentry.xref.manvol" select="0" />
<!-- These put the function name, not NAME, at the top of the ref page -->
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
<!-- Don't include the section number in the hyperlink -->
<xsl:param name="autotoc.label.in.hyperlink" select="0" />

<!-- Conditional inclusion of extra footer content. This is needed to comply
     with Sourceforge.net rules for use of project web space.
     If "dash dash"param footerlogo=1" is used on the xsltproc command line,
     then the extra content will be included.
     NOTE: This is very specific to this project (see group_id).
-->
<xsl:param name="footerlogo" select="0"/>  <!-- Default value -->
<xsl:template name="user.footer.navigation">
  <xsl:if test="$footerlogo != 0">
<div><a href="https://sourceforge.net/projects/phplot/"><img
  src="https://sflogo.sourceforge.net/sflogo.php?group_id=14653&amp;type=13"
  width="120" height="30" border="0" alt="SourceForge.net Logo"
  align="left" /></a>
<p style="font-size: 50%">This version of the manual was produced for the
PHPlot Sourceforge project web service site, which requires the logo on each
page.<br />To download a logo-free copy of the manual, see the
<a href="https://sourceforge.net/projects/phplot/">PHPlot project</a> downloads
area.</p></div>
  </xsl:if>
</xsl:template>

<!-- Ignore scaling on images, which are there to fix PDF rendering. -->
<xsl:param name="ignore.image.scaling" select="1"></xsl:param>

<!-- This tag is used with FOP (PDF output) only, to help pagination.
     It does nothing for XHTML.
-->
<xsl:template match="doc:pagebreak">
</xsl:template>

</xsl:stylesheet>
