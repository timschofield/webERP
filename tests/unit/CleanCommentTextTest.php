<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/includes/MiscFunctions.php';

class CleanCommentTextTest extends TestCase {
	public function testCleansRepeatedlyEscapedComments(): void {
		$Comment = 'what&amp;amp;#039;s going on. \\\\r\\\\nDedication.\\\\r\\\\n' .
			'&amp;amp;quot;No, you are wrong&amp;amp;quot;.';
		$Expected = "what's going on. \nDedication.\n" . '"No, you are wrong".';
		$this->assertSame($Expected, CleanCommentText($Comment));
		$this->assertSame($Expected, CleanCommentText($Expected));
	}

	public function testPreservesOrdinaryTextAndEscapesHtmlForOutput(): void {
		$Comment = "First line\nSecond line & others <script>alert('x')</script>";
		$this->assertSame($Comment, CleanCommentText($Comment));
		$this->assertSame(
			"First line\nSecond line &amp; others &lt;script&gt;alert(&#039;x&#039;)&lt;/script&gt;",
			htmlspecialchars(CleanCommentText($Comment), ENT_QUOTES, 'UTF-8')
		);
	}

	public function testNormalizesDifferentEscapedLineEndings(): void {
		$this->assertSame("one\ntwo\nthree\nfour", CleanCommentText('one\\r\\ntwo\\\\nthree\\\\\\rfour'));
	}
}
