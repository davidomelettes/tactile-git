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
															<td valign="middle" width="452"><p style="font-family: Arial; font-size: 24px; font-weight: bold; padding: 0; margin: 0 0 0 25px; color: #333333;">Password Reset</p></td>
														</tr>
													</table>
												</td>
											</tr>
											<tr>
												<td>
													<table width="490" cellspacing="0" cellpadding="0" style="border-top: 1px solid #CCCCCC;">
														<tr>
															<td>
																<p style="font-family: Arial; font-size: 12px; color: #333; margin: 25px 0 10px 0; padding: 0;">We've reset your password for Tactile CRM as requested. You can now login at <strong><a href="{$login_url}">{$login_url}</a></strong> using the following details:</p>
																<p style="font-family: Arial; font-size: 12px; color: #333; margin: 0 0 10px 0; padding: 0;">Username: <strong>{$User->username}</strong><br/>Password: <strong>{$password}</strong></p>
																<p style="font-family: Arial; font-size: 12px; color: #333; margin: 0 0 10px 0; padding: 0;">If you have any problems logging in, please contact us by emailing our support team on <a href="mailto:support@tactilecrm.com?Subject=Login Issues">support@tactilecrm.com</a> and we'll get back to you ASAP.</p>
																<p style="font-family: Arial; font-size: 12px; color: #999; margin: 0; padding: 0;">--<br />Sent by the Tactile CRM Team</p>
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
