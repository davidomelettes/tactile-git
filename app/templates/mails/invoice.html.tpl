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
															<td valign="middle" width="452"><p style="font-family: Arial; font-size: 24px; font-weight: bold; padding: 0; margin: 0 0 0 25px; color: #333333;">Your Receipt</p></td>
														</tr>
													</table>
												</td>
											</tr>
											<tr>
												<td>
													<table width="490" cellspacing="0" cellpadding="0" style="border-top: 1px solid #CCCCCC;">
														<tr>
															<td>
																<p style="font-family: Arial; font-size: 12px; color: #333; margin: 25px 0 10px 0; padding: 0;">Hi {$firstname},</p>
																<p style="font-family: Arial; font-size: 12px; color: #333; margin: 0 0 10px 0; padding: 0;">Many thanks for paying your Tactile CRM subscription by card, and for your continued support. Details of the payment we took are below:</p>
															</td>
														</tr>
														<tr>
															<td>
																<table width="490" cellspacing="0" cellspacing="0" style="border: 0; margin: 0 0 20px 0;">
																	<tr>
																		<td style="font-family: Arial; font-size: 12px; color: #999; padding-bottom: 0;">Date: <span style="color: #333;">{$payment_date}</span></td>
																	</tr>
																	<tr>
																		<td style="font-family: Arial; font-size: 12px; color: #999; padding-bottom: 0;">Invoice Number: <span style="color: #333;">{$invoice_number}</span></td>
																	</tr>
																	<tr>
																		<td style="font-family: Arial; font-size: 12px; color: #999; padding-bottom: 0;">To: <span style="color: #333;">{$company} ({$firstname} {$surname})</span></td>
																	</tr>
																	<tr>
																		<td style="font-family: Arial; font-size: 12px; color: #999; padding-bottom: 0;">Address: <span style="color: #333;">{$country}</span></td>
																	</tr>
																</table>
																<table width="490" cellspacing="0" cellspacing="0" style="border: 0; margin: 0 0 20px 0; padding: 0;">
																	<tr>
																		<td style="border-bottom: 1px solid #ccc; font-family: Arial; font-size: 12px; color: #999;">Description</td>
																		<td align="right" style="border-bottom: 1px solid #ccc; font-family: Arial; font-size: 12px; color: #999; padding-bottom: 5px;">Quantity</td>
																		<td align="right" style="border-bottom: 1px solid #ccc; font-family: Arial; font-size: 12px; color: #999; padding-bottom: 5px;">Amount</td>
																	</tr>
																	<tr>
																		<td style="font-family: Arial; font-size: 12px; color: #333; padding-top: 5px; padding-bottom: 5px;">{$description}</td>
																		<td align="right" style="font-family: Arial; font-size: 12px; color: #333; padding-top: 5px; padding-bottom: 5px;">{$quantity}</td>
																		<td align="right" style="font-family: Arial; font-size: 12px; color: #333; padding-top: 5px; padding-bottom: 5px;">{$line_amount}</td>
																	<tr>
																	<tr>
																		<td colspan="2" align="right" style="font-family: Arial; font-size: 12px; color: #999;">Sub Total</td>
																		<td align="right" style="font-family: Arial; font-size: 12px; color: #999;">{$sub_total}</td>
																	</tr>
																	<tr>
																		<td colspan="2" align="right" style="font-family: Arial; font-size: 12px; color: #999;">VAT @ {$vat_rate}%</td>
																		<td align="right" style="font-family: Arial; font-size: 12px; color: #999;">{$vat}</td>
																	</tr>
																	<tr>
																		<td colspan="2" align="right" style="padding-bottom: 5px;"><strong>Total</strong></td>
																		<td align="right" style="padding-bottom: 5px;"><strong>{$total}</strong></td>
																	</tr>
																</table>
															</td>
														</tr>
														<tr>
															<td>
																<p style="font-family: Arial; font-size: 12px; color: #333; margin: 0 0 10px 0; padding: 0;">This payment was taken on <strong>{$payment_date}</strong> in <strong>Pounds Sterling</strong> with authorisation code <strong>{$auth_code}</strong> it was a sale transaction - you can update your details we have on file in <a href="http://{$site_address}.tactilecrm.com//account/account_details/">your account area</a>.</p>
																<p style="font-family: Arial; font-size: 12px; color: #333; margin: 0 0 10px 0; padding: 0;">Many Thanks<br />The Tactile CRM Team</p>
																<p style="font-family: Arial; font-size: 12px; color: #999; margin: 0; padding: 0;">Tactile CRM is a trading name of omelett.es ltd, you can call us on +44 (0)207 183 6677. Our company registration number is 06795765 and omelett.es ltd is registered in England and Wales with registered office at 7200 The Quorum, Oxford Business Park North, Garsington Road, Oxford, OX4 2JZ, UK. VAT number GB 944 6741 95. Please visit <a href="http://www.tactilecrm.com">www.tactilecrm.com</a> for our latest Terms &amp; Conditions and
Refund Policy.</p>
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
