<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.emailcloak
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\String\StringHelper;

/**
 * Email cloack plugin class.
 *
 * @since  1.5
 */
class PlgContentElectroneum_donate extends JPlugin
{
    public function __construct(& $subject, $config)
	{
		 parent::__construct($subject, $config);
		 
		 if(JFactory::getApplication()->isAdmin()) {
			return;
		}
		
		$ajaxtask = JRequest::getVar("ajaxtask");
		if($ajaxtask == "thankspage")
		{
			
			$etn_params = $this->params;
			
			$successurl = $etn_params->get("successurl", "index.php");
			$thanksmsg = $etn_params->get("thanksmsg", "Thanks for you Donation !");
			
			$thanksurl =  JRoute::_($successurl);
			
			$app = JFactory::getApplication();
			$app->redirect($thanksurl, $thanksmsg);
			
		}
		if($ajaxtask == 'getresponse')
		{
			require_once("plugins/content/electroneum_donate/src/Vendor.php");
			require_once("plugins/content/electroneum_donate/src/Exception/VendorException.php");
			

			$etn_params = $this->params;
			
			$apikey = $etn_params->get("apikey");
			$secret = $etn_params->get("secret");
			$outlet = $etn_params->get("outlet");
			
			 $etn = JRequest::getVar("etn"); 
			 $paymentid = JRequest::getVar("paymentid"); 
			 
			 $vendor = new \Electroneum\Vendor\Vendor($apikey, $secret);
			 
			 $payload = array();
			 $payload['payment_id'] = $paymentid;
 	         $payload['vendor_address'] = 'etn-it-'.$outlet;
			 
			 $result = $vendor->checkPaymentPoll(json_encode($payload));
			 
			 $return = array();
	 	     if($result['status'] == 1) 
			 {
				 $return['success'] = 1;
				 $return['amount'] = $result['amount'];
				 $result['message'] = '';
			 }
			 else if (!empty($result['message']))  
			 {
				 $return['success'] = 0;
				 $return['message'] = $result['message'];
			 }
			 else
			 {
				  $return['success'] = 0;
				  $return['message'] = 'Unknown Error was found';
			 }
			echo json_encode($return);
			exit;
		}
		if($ajaxtask == "getqr")
		{
			$amtval = JRequest::getVar("amtval");
			
			require_once("plugins/content/electroneum_donate/src/Vendor.php");
			require_once("plugins/content/electroneum_donate/src/Exception/VendorException.php");
			
			
			$etn_params = $this->params;
			
			$apikey = $etn_params->get("apikey");
			$secret = $etn_params->get("secret");
			$outlet = $etn_params->get("outlet");
			
			
			
		
			
			$vendor = new \Electroneum\Vendor\Vendor($apikey, $secret);
			$qrImgUrl = $vendor->getQr($amtval, $currency, $outlet);
			
			
			$formurl = JRoute::_('index.php?ajaxtask=thankspage');

			
			$html .= '<form class="uk-form uk-form-horizontal uk-text-center" style=" text-align:center;" id="electronium_payform" method="post" action="'.$formurl.'">';
			$html .= '<div class="uk-form-row">';
			$html .= '<div id="error_div"></div>';
			$html .= '</div>';
			$html .= '<div id="paymentqr_div">';
				$html .= '<div class="uk-form-row">';
				$html .= "<p class='uk-text-primary uk-text-large uk-text-bold'>Payment for " . $vendor->getEtn(). " ETN to outlet</p>";
				
				$html .= '<div class="uk-text-center uk-margin-bottom" style="background-color: rgb(255, 255, 255); margin:0 auto; padding-bottom: 5px; border-color: rgb(255, 255, 255); border-style: solid; border-width: 12px 12px 6px; border-image: none 100% / 1 / 0 stretch; border-radius: 8px; box-shadow: rgba(50, 50, 50, 0.2) 0px 2px 8px 0px; width: 240px; text-decoration: none; color: rgb(51, 51, 51); text-align: center; cursor: pointer;">';
	
					$html .= '<div style="position: relative; box-sizing: content-box; border: 1px solid #24aaca;">';
						 $html .= "<img id='qrimage' src=\"$qrImgUrl\" style='box-sizing: border-box; border: 8px solid rgb(255, 255, 255); margin-bottom: 10px; width: 100%;' />"; 
					 	 $html .= '<img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDIyLjEuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPgo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IgoJIHZpZXdCb3g9IjAgMCAxMTg1LjQgMjYwLjMiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDExODUuNCAyNjAuMzsiIHhtbDpzcGFjZT0icHJlc2VydmUiPgo8c3R5bGUgdHlwZT0idGV4dC9jc3MiPgoJLnN0MHtmaWxsOiMwQzM1NDg7fQoJLnN0MXtmaWxsOiMyQUIxRjM7fQo8L3N0eWxlPgo8dGl0bGU+YWx0LWNvbG91cnM8L3RpdGxlPgo8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMzk5LjcsMTE5LjJ2MjYuNGgtNjMuOXYxNi4xYzAuMSwyLjcsMi4yLDQuOCw0LjksNC45aDU5djEwLjNoLTU5Yy04LjQsMC0xNS4yLTYuOC0xNS4yLTE1LjJjMCwwLDAsMCwwLDAKCXYtNDIuNWMwLTguNCw2LjgtMTUuMiwxNS4yLTE1LjJjMCwwLDAsMCwwLDBoNDMuN0MzOTIuOCwxMDMuOSwzOTkuNiwxMTAuNywzOTkuNywxMTkuMkMzOTkuNywxMTkuMSwzOTkuNywxMTkuMSwzOTkuNywxMTkuMnoKCSBNMzg5LjIsMTM1LjN2LTE2LjFjMC0yLjctMi4yLTQuOS00LjktNC45aC00My43Yy0yLjcsMC4xLTQuOCwyLjItNC45LDQuOXYxNi4xSDM4OS4yeiIvPgo8cGF0aCBjbGFzcz0ic3QwIiBkPSJNNDIwLjIsODAuMnY4MS41YzAuMSwyLjcsMi4yLDQuOCw0LjksNC45aDEyLjN2MTAuM2gtMTIuM2MtOC40LDAtMTUuMi02LjgtMTUuMi0xNS4yYzAsMCwwLDAsMCwwVjgwLjJINDIwLjJ6IgoJLz4KPHBhdGggY2xhc3M9InN0MCIgZD0iTTUyMCwxMTkuMnYyNi40aC02My45djE2LjFjMC4xLDIuNywyLjIsNC44LDQuOSw0LjloNTl2MTAuM2gtNTljLTguNCwwLTE1LjItNi44LTE1LjItMTUuMmMwLDAsMCwwLDAsMHYtNDIuNQoJYzAtOC40LDYuOC0xNS4yLDE1LjItMTUuMmMwLDAsMCwwLDAsMGg0My43QzUxMy4xLDEwMy45LDUyMCwxMTAuNyw1MjAsMTE5LjJDNTIwLDExOS4xLDUyMCwxMTkuMSw1MjAsMTE5LjJ6IE01MDkuNywxMzUuM3YtMTYuMQoJYzAtMi43LTIuMi00LjktNC45LTQuOWgtNDMuN2MtMi43LDAuMS00LjgsMi4yLTQuOSw0Ljl2MTYuMUg1MDkuN3oiLz4KPHBhdGggY2xhc3M9InN0MCIgZD0iTTYwNC40LDE2Ni42djEwLjNoLTU5Yy04LjQsMC0xNS4yLTYuOC0xNS4yLTE1LjJjMCwwLDAsMCwwLDB2LTQyLjVjMC04LjQsNi44LTE1LjIsMTUuMi0xNS4yYzAsMCwwLDAsMCwwaDU4LjgKCXYxMC4zaC01OC44Yy0yLjcsMC4xLTQuOCwyLjItNC45LDQuOXY0Mi41YzAuMSwyLjcsMi4yLDQuOCw0LjksNC45TDYwNC40LDE2Ni42eiIvPgo8cGF0aCBjbGFzcz0ic3QwIiBkPSJNNjI0LjksMTE0LjN2NDcuM2MwLjEsMi43LDIuMiw0LjgsNC45LDQuOWgyNi42djEwLjNoLTI2LjZjLTguNCwwLTE1LjEtNi44LTE1LjItMTUuMWMwLDAsMC0wLjEsMC0wLjFWODAuMQoJSDYyNVYxMDRoMzEuNXYxMC4zTDYyNC45LDExNC4zeiIvPgo8cGF0aCBjbGFzcz0ic3QwIiBkPSJNNzIyLjEsMTA0djEwLjNoLTQwLjljLTIuNywwLjEtNC44LDIuMi00LjksNC45djU3LjZINjY2di01Ny42YzAtOC40LDYuOC0xNS4yLDE1LjItMTUuMmMwLDAsMCwwLDAsMEg3MjIuMXoiCgkvPgo8cGF0aCBjbGFzcz0ic3QwIiBkPSJNNzg4LjQsMTA0YzguNCwwLDE1LjMsNi43LDE1LjMsMTUuMWMwLDAsMCwwLDAsMC4xdjQyLjVjMCw4LjQtNi44LDE1LjItMTUuMiwxNS4yYzAsMCwwLDAtMC4xLDBoLTQzLjcKCWMtOC40LDAtMTUuMi02LjgtMTUuMi0xNS4yYzAsMCwwLDAsMCwwdi00Mi41YzAtOC40LDYuOC0xNS4yLDE1LjItMTUuMmMwLDAsMCwwLDAsMEg3ODguNHogTTc0NC43LDExNC4zYy0yLjcsMC4xLTQuOCwyLjItNC45LDQuOQoJdjQyLjVjMC4xLDIuNywyLjIsNC44LDQuOSw0LjloNDMuN2MyLjcsMCw0LjktMi4yLDQuOS00Ljl2LTQyLjVjMC0yLjctMi4yLTQuOS00LjktNC45TDc0NC43LDExNC4zeiIvPgo8cGF0aCBjbGFzcz0ic3QwIiBkPSJNODg5LjEsMTE5LjJ2NTcuNmgtMTAuM3YtNTcuNmMtMC4xLTIuNy0yLjItNC44LTQuOS00LjloLTQzLjdjLTIuNywwLTQuOSwyLjItNSw0Ljl2NTcuNmgtMTAuM1YxMDRoNTkKCWM4LjQsMCwxNS4yLDYuNywxNS4yLDE1LjFDODg5LjEsMTE5LjEsODg5LjEsMTE5LjEsODg5LjEsMTE5LjJ6Ii8+CjxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik05NzYuMiwxMTkuMnYyNi40aC02My45djE2LjFjMC4xLDIuNywyLjIsNC44LDQuOSw0LjloNTl2MTAuM2gtNTljLTguNCwwLTE1LjItNi44LTE1LjItMTUuMmMwLDAsMCwwLDAsMAoJdi00Mi41YzAtOC40LDYuOC0xNS4yLDE1LjItMTUuMmMwLDAsMCwwLDAsMGg0My43Qzk2OS4zLDEwMy45LDk3Ni4yLDExMC43LDk3Ni4yLDExOS4yQzk3Ni4yLDExOS4xLDk3Ni4yLDExOS4xLDk3Ni4yLDExOS4yegoJIE05NjUuOCwxMzUuM3YtMTYuMWMwLTIuNy0yLjItNC45LTQuOS00LjloLTQzLjdjLTIuNywwLjEtNC44LDIuMi00LjksNC45djE2LjFIOTY1Ljh6Ii8+CjxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0xMDYzLjMsMTA0djU3LjZjMCw4LjQtNi44LDE1LjItMTUuMiwxNS4yYzAsMCwwLDAtMC4xLDBoLTQzLjdjLTguNCwwLTE1LjItNi44LTE1LjItMTUuMmMwLDAsMCwwLDAsMFYxMDQKCWgxMC4zdjU3LjZjMC4xLDIuNywyLjIsNC44LDQuOSw0LjloNDMuN2MyLjcsMCw0LjktMi4yLDUtNC45VjEwNEgxMDYzLjN6Ii8+CjxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0xMTg1LjQsMTE5LjJ2NTcuNmgtMTAuMnYtNTcuNmMtMC4xLTIuNy0yLjMtNC45LTUtNC45aC0zMC4zYy0yLjcsMC00LjksMi4yLTQuOSw0Ljl2NTcuNmgtMTAuM3YtNTcuNgoJYy0wLjEtMi43LTIuMi00LjgtNC45LTQuOWgtMzAuNGMtMi43LDAuMS00LjgsMi4yLTQuOSw0Ljl2NTcuNmgtMTAuNFYxMDRoOTYuMmM4LjMsMCwxNS4xLDYuNywxNS4yLDE1CglDMTE4NS40LDExOSwxMTg1LjQsMTE5LjEsMTE4NS40LDExOS4yeiIvPgo8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMTc1LjksMTAwLjNsMjEuNiwxNi40bDEzLjcsMTAuM2wtMTUuMiw3LjhsLTMwLjcsMTUuN2wxMC41LDcuM2wxNC43LDEwLjJsLTE1LjksOC4ybC05Ny41LDUwLjMKCWM1My4xLDI5LjIsMTE5LjksOS45LDE0OS4xLTQzLjNjMjEuOC0zOS42LDE3LjEtODguNS0xMS45LTEyMy4yTDE3NS45LDEwMC4zeiIvPgo8cGF0aCBjbGFzcz0ic3QwIiBkPSJNODQuMSwxNjUuOGwtMjEuNi0xNi40bC0xMy43LTEwLjNsMTUuMi03LjhsMzAuNy0xNS43bC0xMC41LTcuM0w2OS41LDk4LjFsMTUuOS04LjJsMTAyLjctNTMKCUMxMzYuNyw0LjgsNjguOSwyMC41LDM2LjksNzEuOUMxMSwxMTMuNCwxNS43LDE2Nyw0OC4zLDIwMy40TDg0LjEsMTY1Ljh6Ii8+CjxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik00MS43LDIxMC42Qy0yLjgsMTYxLjgsMC44LDg2LjIsNDkuNSw0MS44QzkwLjcsNC4zLDE1Mi4zLDAuMiwxOTguMSwzMmwxMC43LTUuNUMxNTEuNS0xNyw2OS45LTUuOCwyNi40LDUxLjUKCWMtMzguMSw1MC4yLTM0LjcsMTIwLjUsOCwxNjYuOUw0MS43LDIxMC42eiIvPgo8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMjIxLDUyLjhjNDIuOCw1MC4yLDM2LjgsMTI1LjYtMTMuNCwxNjguNGMtMzkuNiwzMy43LTk2LjUsMzgtMTQwLjcsMTAuNEw1NiwyMzcuMgoJYzU5LjEsNDAuOSwxNDAuMSwyNi4yLDE4MS4xLTMyLjljMzMuOC00OC44LDMwLjMtMTE0LjQtOC43LTE1OS4zTDIyMSw1Mi44eiIvPgo8cG9seWdvbiBjbGFzcz0ic3QxIiBwb2ludHM9IjY4LjksMTQwLjkgMTAwLjEsMTY0LjUgMjkuNiwyMzguOCAxNjkuNywxNjYuNSAxNDQuNCwxNDkuMSAxOTEuMSwxMjUuMiAxNTkuOCwxMDEuNiAyMzAuMywyNy40IAoJOTAuMyw5OS42IDExNS42LDExNy4xICIvPgo8L3N2Zz4K" style="width: 110px; box-sizing: content-box; background-color: rgb(255, 255, 255); padding: 0px 8px; position: absolute; bottom: -13px; left: 50%; transform: translateX(-50%);">';
					$html .= '</div>';
	
			      $html .= '<img src="'.JURI::Base().'plugins/content/electroneum_donate/src/loading.gif" style="height:55px; margin-top:10px;" />';
				  $html .= '<div>Scan with the app or click to pay</div>';
	
 	           $html .= '</div>';

				
			$html .= '</div>';
			
			$html .= '<div class="uk-form-row">';
			$html .= '<button type="button" onclick="checkelectroneumresponse()" class="uk-button uk-button-primary">'.JText::_("Confirm").'</button>';
			$html .= '</div>';	
			

			$html .= '<input type="hidden" name="etn" id="etn" value="'.$vendor->getEtn().'" />'; 
			$html .= '<input type="hidden" name="paymentid" id="paymentid" value="'.$vendor->getPaymentId().'" />'; 
			$html .= '<input type="hidden" name="apikey" id="apikey" value="'.$apikey.'" />';
			$html .= '<input type="hidden" name="secret" id="secret" value="'.$secret.'" />';
			$html .= '<input type="hidden" name="outlet" id="outlet" value="'.$outlet.'" />';
			$html .= '<input type="hidden" name="ajaxtask" id="ajaxtask" value="getresponse" />';
			$html .= '<input type="submit" name="submit_btn" value="submitbtn" style="display:none;" />';
			$html .= '</form>'."\n";
			
			$return = array();
			$return['html'] = $html;
			
			ob_clean();
			echo json_encode($return);
			exit;

		}
		if($ajaxtask == "getrespnose")
		{
		}
		
	}
	
	 public function onContentPrepare($context, &$article, &$params, $page = 0)
	 {
	 

		if ($context === 'com_finder.indexer')  
		{
			return true;   
		}
		
		if (strpos($article->text, 'etndonate') === false && strpos($article->text, 'etndonate') === false)
		{
			return true;
		}
	

			$etn_params = $this->params;
			

			
			
			
			$apikey = $etn_params->get("apikey");
			$secret = $etn_params->get("secret");
			$outlet = $etn_params->get("outlet");
			
			$logo = $etn_params->get("logo", '');
			$description = $etn_params->get("description", '');
			$btntxt = $etn_params->get("btntxt", 'Donate');
			$color =   $etn_params->get("color", '#00ff00');
			$electroneum_logo = $etn_params->get("electroneum_logo", 1);

			// Expression to search for
			$pattern = '#\{etndonate}#i';
			// Found matches
			if (preg_match_all($pattern, $article->text, $matches)) {
				
				
			
				// Disable caching
				$cache = JFactory::getCache('com_content');
				$cache->setCaching(false);
				
		
				
				foreach ($matches[0] as $i => $fullMatch)
				{
					
					//$document = JFactory::getDocument("site");
					JHtml::_('jquery.framework');
					JHtml::script(Juri::base().'plugins/content/electroneum_donate/src/electroneum.js'); 
					JHtml::stylesheet(Juri::base().'plugins/content/electroneum_donate/src/electroneum.css'); 

					
					    
					 	if(empty($outlet) || empty($secret) || empty($apikey))
						{
							$html = '';
							
							$html .= '<div id="electroneum_pay_div" style="color:#ff0000;">';
							$html .= 'Please fill out your Electroneum vendor data in plugin settings';
							$html .= '</div>';
						}	
						else
						{		
							 
							$html = '';
							
							$html .= '<div id="electroneum_pay_div">';
							
							$html .= '<div id="firstdiv_donate">';
								if($logo != "")
								{
									$html .= '<div class="logo" style="text-align:center;">';
									$html .= '<img src="'.JURI::Base().$logo.'" />';
									$html .= '</div>';
								}
								if($description != "")
								{
									$html .= '<div class="description" style="text-align:center; margin:10px 0;">';
									$html .= '<p>'.$description.'</p>';
									$html .= '</div>';
								}
								
	
								$html .= '<div class="amountdiv" style="text-align:center; margin:10px 0;">';
								$html .= '<input type="text" id="donnation_amt" placeholder="Amount" value="" style="text-align:center; padding:0px; width:100%; height:45px; border:1px solid #f1f1f1;" />';
								$html .= '</div>';

								
								$html .= '<div class="button_div" style="text-align:center; margin:10px 0;">';
								$html .= '<button type="button" onclick="donateamount()" style="text-align:center; background:'.$color.'; cursor:pointer; height:45px; padding:10px; width:100%; color:#fff; border:1px none;"/>'.$btntxt.'</button>';
								$html .= '<input type="hidden" name="sitebaseurl" id="sitebaseurl" value="'.JURI::getInstance().'" />';
								$html .= '</div>';
							$html .= '</div>';
							$html .= '<div id="seconddiv" style="display:none;">';
							$html .= '';
							$html .= '</div>';
							
							$html .= '<div id="thirddiv" style="display:none;">';
							$html .= '<svg id="checkmark_svg" style="display:none;" class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"><circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/><path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/></svg>';
							$html .= '</div>';
							if($electroneum_logo)	
							{
								 $html .= '<div class="description" style="text-align:center; margin:10px 0;">';
								 $html .= '<a href="https://electroneum.com" target="_blank"><img src="'.JURI::Base().'plugins/content/electroneum_donate/src/electroneum.png" style="width:170px;" /></a>';
								 $html .= '</div>';
							}
							
							$html .= '</div>';
							
					    }
						

						$article->text = str_replace($fullMatch, $html, $article->text);
						

					 
				}
			}
			
			
	}
	
	function onContentAfterDisplay($context, $article, $params, $limitstart = 0)
	{

		
	    if(JFactory::getApplication()->isAdmin()) {
			return;
		}
		 
		//add your plugin codes here
		return '';
		//return a string value. Returned value from this event will be displayed in a placeholder. 
                // Most templates display this placeholder after the article separator.
	}
	/**
	 * Loads and renders the module
	 *
	 * @param   string  $position  The position assigned to the module
	 * @param   string  $style     The style assigned to the module
	 *
	 * @return  mixed
	 *

	 * @since   1.6
	 */
	protected function _load($position, $style = 'none')
	{
		self::$modules[$position] = '';
		$document = JFactory::getDocument();
		$renderer = $document->loadRenderer('module');
		$modules  = JModuleHelper::getModules($position);
		$etn_params   = array('style' => $style);
		ob_start();

		foreach ($modules as $module)
		{
			echo $renderer->render($module, $etn_params);
		}

		self::$modules[$position] = ob_get_clean();

		return self::$modules[$position];
	}

	/**
	 * This is always going to get the first instance of the module type unless
	 * there is a title.
	 *
	 * @param   string  $module  The module title
	 * @param   string  $title   The title of the module
	 * @param   string  $style   The style of the module
	 *
	 * @return  mixed
	 *
	 * @since   1.6
	 */
	protected function _loadmod($module, $title, $style = 'none')
	{
		self::$mods[$module] = '';
		$document = JFactory::getDocument();
		$renderer = $document->loadRenderer('module');
		$mod      = JModuleHelper::getModule($module, $title);

		// If the module without the mod_ isn't found, try it with mod_.
		// This allows people to enter it either way in the content
		if (!isset($mod))
		{
			$name = 'mod_' . $module;
			$mod  = JModuleHelper::getModule($name, $title);
		}

		$etn_params = array('style' => $style);
		ob_start();

		if ($mod->id)
		{
			echo $renderer->render($mod, $etn_params);
		}

		self::$mods[$module] = ob_get_clean();

		return self::$mods[$module];
	}

}
