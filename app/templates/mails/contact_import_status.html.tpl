<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Tactile CRM Email</title>
</head>
<body style="padding: 0; margin: 0; background-color: #EEEEEE;">
	<table width="100%" cellspacing="0" cellpadding="0" bgcolor="#EEEEEE">
		<tr>
			<td align="center">
				<table width="600" cellspacing="0" cellpadding="25">
					<tr>
						<td>
							<table width="550" cellspacing="0" cellpadding="5" bgcolor="#CCCCCC">
								<tr>
									<td>
										<table width="540" cellspacing="25" cellpadding="0" bgcolor="#FFFFFF">
											<tr>
												<td>
													<table width="490" cellspacing="0" cellpadding="0">
														<tr>
															<td width="38"><img src="http://www.tactilecrm.com/graphics/emaillogo.png" width="38" height="50" alt="" border="0" style="padding: 0; margin: 0;" /></td>
															<td valign="middle" width="452"><p style="font-family: Arial; font-size: 24px; font-weight: bold; padding: 0; margin: 0 0 0 25px; color: #333333;">Import Completed</p></td>
														</tr>
													</table>
												</td>
											</tr>
											<tr>
												<td>
													<table width="490" cellspacing="0" cellpadding="0" style="border-top: 1px solid #CCCCCC;">
														<tr>
															<td>
																<p style="font-family: Arial; font-size: 12px; color: #333; margin: 25px 0 10px 0; padding: 0;">The import you requested has now been completed.</p>
																<p style="font-family: Arial; font-size: 12px; color: #333; margin: 0 0 10px 0; padding: 0;">During the import, {$num_imported} records (Organisations and People) were created, {if $num_errors eq 0}and there were no problems.</p>{else}but there were some details we couldn't do anything with.</p>
																<p style="font-family: Arial; font-size: 12px; color: #333; margin: 0 0 10px 0; padding: 0;">Attached is a list of records that had problems, along with a short description of what the problem(s) is/are. We aim to make the import process as intelligent as possible, so any feedback on problem-data is appreciated (drop us an <a href="mailto:support@tactilecrm.com?Subject=Imports">email to support@tactilecrm.com</a>).</p>{/if}
																{if $import_link}
																<p style="font-family: Arial; font-size: 12px; color: #333; margin: 0 0 10px 0; padding: 0;">You can find the results here: <a href="{$import_link}">{$import_link}</a></p>
																{/if}
																<p style="font-family: Arial; font-size: 12px; color: #999; margin: 0 0 10px 0; padding: 0;">--<br />Sent by the Tactile CRM Team</p>
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>

