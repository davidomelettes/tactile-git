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
															<td valign="middle" width="452"><p style="font-family: Arial; font-size: 24px; font-weight: bold; padding: 0; margin: 0 0 0 25px; color: #333333;">Campaign Monitor Upload Complete</p></td>
														</tr>
													</table>
												</td>
											</tr>
											<tr>
												<td>
													<table width="490" cellspacing="0" cellpadding="0" style="border-top: 1px solid #CCCCCC;">
														<tr>
															<td>
																<p style="font-family: Arial; font-size: 12px; color: #333; margin: 25px 0 10px 0; padding: 0;">Your export to Campaign Monitor has completed. Of the {$total} People selected, we were able to subscribe {if $total == $successes}all{else}{$successes}{/if} of them to the list called "{$list}".</p>
																<p style="font-family: Arial; font-size: 12px; color: #333; margin: 0 0 10px 0; padding: 0;">

{foreach from=$messages item=count key=msg name=msg}
We were unable to subscribe {$count} {if $count > 1}People{else}Person{/if} because of the following problem: {$msg}
{if !$smarty.foreach.msg.last}<br />{/if}
{/foreach}
</p>
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
