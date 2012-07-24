<?php

/* $Id$*/

echo '<table width="100%" id="footer">
		<tr>
			<td style="width:33%"></td><td class="footer">
				<img src="'. $rootpath . '/' . $_SESSION['LogoFile'] . '" width="120" alt="webERP" title="webERP ' . _('Copyright') . ' &copy; weberp.org - ' . date('Y') . '" />
			</td>
			<td class="footer" style="text-align: right;width:33%">'. DisplayDateTime() .'</td>
		</tr>
		<tr>
			<td colspan="3" class="footer">webERP ' ._('version') .' ' . $_SESSION['VersionNumber'] . '</td>
		</tr>
	</table>
	</div>
	</body>
	</html>';

?>
