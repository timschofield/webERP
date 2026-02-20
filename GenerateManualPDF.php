<?php
/**
 * Generate PDF version of the webERP Manual
 * Can be run via web browser or command line
 *
 * Usage:
 *   Web: GenerateManualPDF.php
 *   CLI: php GenerateManualPDF.php
 */

$PageSecurity = 15; // System Administrator only

// Determine if running from CLI
$IsCommandLine = (php_sapi_name() === 'cli');

if (!$IsCommandLine) {
	require(__DIR__ . '/includes/session.php');
} else {
	// CLI mode - minimal setup
	$PathPrefix = __DIR__ . '/';
	if (isset($argv[1])) {
		$Language = $argv[1];
	} else {
		$Language = 'en_GB.utf8';
	}
}

// Include DomPDF
require(__DIR__ . '/vendor/autoload.php');
use Dompdf\Dompdf;

// Set up DomPDF options
include(__DIR__ . '/includes/SetDomPDFOptions.php');

// Set language if not set
if (!isset($Language)) {
	$Language = 'en_GB.utf8';
}

// Determine manual outline and CSS
$ManualOutline = 'locale/' . $Language . '/Manual/ManualOutline.php';
if (!file_exists($ManualOutline)) {
	$ManualOutline = 'doc/Manual/ManualOutline.php';
}

$ManualStyle = 'locale/' . $Language . '/Manual/css/manual.css';
if (!file_exists($ManualStyle)) {
	$ManualStyle = 'doc/Manual/css/manual.css';
}

// Load the table of contents
include($ManualOutline);

// Read the CSS content to embed in PDF
$CSSContent = file_get_contents($ManualStyle);

// Start building HTML for PDF
$HTML = '<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>webERP Manual</title>
	<style>
		' . $CSSContent . '

		/* Additional PDF-specific styles */
		body {
			font-family: DejaVu Sans, Arial, sans-serif;
			font-size: 10pt;
			margin: 20px;
		}

		h1 {
			page-break-before: always;
			margin-top: 0;
		}

		h1:first-of-type {
			page-break-before: auto;
		}

		img {
			max-width: 100%;
			height: auto;
		}

		table {
			border-collapse: collapse;
			width: 100%;
			margin-bottom: 10px;
		}

		table, th, td {
			border: 1px solid #ccc;
		}

		th, td {
			padding: 5px;
			text-align: left;
		}

		pre {
			background-color: #f5f5f5;
			padding: 10px;
			border: 1px solid #ccc;
			overflow-wrap: break-word;
			white-space: pre-wrap;
		}

		code {
			font-family: "Courier New", monospace;
			font-size: 9pt;
		}

		/* Page numbers footer */
		@page { margin: 2cm; }
		.footer {
			position: fixed;
			bottom: 0;
			left: 0;
			right: 0;
			text-align: right;
			font-size: 9pt;
			color: #666;
		}
		.footer .pagenum:before { content: counter(page); }
		.footer .totalpages:before { content: counter(pages); }
	</style>
</head>
<body><div class="footer">Page <span class="pagenum"></span></div>';

// Add title page
$HTML .= '
<div style="text-align: center; margin-top: 200px; page-break-after: always;">
	<h1 style="font-size: 36pt; page-break-before: auto;">webERP Manual</h1>
	<p style="font-size: 14pt; margin-top: 50px;">Complete User and Administrator Guide</p>
	<p style="font-size: 12pt; margin-top: 30px;">Generated: ' . date('Y-m-d H:i:s') . '</p>
	<p style="font-size: 12pt;">Language: ' . $Language . '</p>
</div>';

// Add table of contents
$HTML .= '<h1>Table of Contents</h1>';
$HTML .= '<div style="page-break-after: always;">';

$pageNum = 1;
foreach ($TOC_Array['TableOfContents'] as $Name => $FullName) {
	$SectionTitle = is_array($FullName) ? $FullName[0] : $FullName;
	$HTML .= '<p><strong>' . htmlspecialchars($SectionTitle) . '</strong></p>';

	if (is_array($FullName)) {
		$HTML .= '<ul>';
		foreach ($FullName as $index => $SubSection) {
			if ($index > 0) { // Skip the first element (section title)
				$HTML .= '<li>' . htmlspecialchars($SubSection) . '</li>';
			}
		}
		$HTML .= '</ul>';
	}
}
$HTML .= '</div>';

// Function to process manual files and convert relative paths for images
function ProcessManualContent($FilePath, $Language) {
	if (!file_exists($FilePath)) {
		return '<p><em>Content not available for this section.</em></p>';
	}

	$Content = file_get_contents($FilePath);

	// Convert relative image paths to absolute paths
	// Pattern: src="doc/Manual/images/...
	$Content = preg_replace(
		'/src="doc\/Manual\/images\//i',
		'src="' . __DIR__ . '/doc/Manual/images/',
		$Content
	);

	// Pattern: src="images/...
	$Content = preg_replace(
		'/src="images\//i',
		'src="' . __DIR__ . '/doc/Manual/images/',
		$Content
	);

	// Remove any navigation links that don't make sense in PDF
	$Content = preg_replace('/<a[^>]*href="#top"[^>]*>.*?<\/a>/i', '', $Content);
	$Content = preg_replace('/<a[^>]*href="#bottom"[^>]*>.*?<\/a>/i', '', $Content);
	$Content = preg_replace('/<a[^>]*href="ManualContents\.php[^"]*"[^>]*>.*?<\/a>/i', '', $Content);

	return $Content;
}

// Process each manual section
foreach ($TOC_Array['TableOfContents'] as $Name => $FullName) {
	if ($Name == 'APIFunctions') {
//		$FileName = 'Manual' . $Name . '.php';
	} else {
		$FileName = 'Manual' . $Name . '.html';
	}

	// Check for localized version first
	$ManualPage = 'locale/' . $Language . '/Manual/' . $FileName;
	if (!file_exists($ManualPage)) {
		$ManualPage = 'doc/Manual/' . $FileName;
	}

	if (file_exists($ManualPage)) {
		if ($Name == 'APIFunctions') {
			// Handle PHP file specially
			ob_start();
			$PathPrefix = __DIR__ . '/';
			$RootPath = '/';
			include($ManualPage);
			$Content = ob_get_clean();
			$HTML .= ProcessManualContent('php://memory', $Language);
			$HTML .= $Content;
		} else {
			$HTML .= ProcessManualContent($ManualPage, $Language);
		}
	} else {
		// Section not found
		$SectionTitle = is_array($FullName) ? $FullName[0] : $FullName;
		$HTML .= '<h1>' . htmlspecialchars($SectionTitle) . '</h1>';
		$HTML .= '<p><em>Content not yet available for this section.</em></p>';
	}
}

$HTML .= '
</body>
</html>';

try {
	$DomPDF = new Dompdf($DomPDFOptions);
	$DomPDF->loadHtml($HTML);

	// Set paper size
	$DomPDF->setPaper('A4', 'portrait');

	// Render the HTML as PDF
	$DomPDF->render();

	// Generate filename
	$FileName = 'doc/webERP_Manual_' . str_replace('.', '_', $Language) . '.pdf';

	if ($IsCommandLine) {
		// Save to file in CLI mode
		$OutputPath = __DIR__ . '/' . $FileName;
		file_put_contents($OutputPath, $DomPDF->output());
		echo "PDF generated successfully: " . $OutputPath . "\n";
	} else {
		// Stream to browser
		$DomPDF->stream($FileName, array(
			"Attachment" => false
		));
	}

} catch (Exception $e) {
	if ($IsCommandLine) {
		echo "Error generating PDF: " . $e->getMessage() . "\n";
		exit(1);
	} else {
		$Title = 'Error Generating Manual PDF';
		include('includes/header.php');
		echo '<div class="centre">';
		echo '<h2>Error Generating PDF</h2>';
		echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
		echo '<p><a href="ManualContents.php">Return to Manual</a></p>';
		echo '</div>';
		include('includes/footer.php');
	}
}
?>