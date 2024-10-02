<?php
require_once WACT_ROOT . 'controller/form.inc.php';
require_once WACT_ROOT . 'view/view.inc.php';

require_once APP_ROOT . 'reservation/reservation.model.php';

require_once WACT_ROOT . 'util/arraydataset.inc.php';
require_once WACT_ROOT . 'template/widgets/widgets.inc.php';

class HTMLTextWidget extends TextWidget {
	function render(){
		echo ( $this->text );
	}
}
class ShowView extends View {

	function ShowView($TemplateFile) {
		parent::View($TemplateFile);
	}

	function prepare(&$controller, &$request, &$responseModel) {
		//Populating current username's occupations list
		if ($responseModel->hasProperty('mutationname')) {
			$displayname = $responseModel->get('mutationname');
		} else {
			$displayname = $responseModel->get('username');
		}
        $occupationsList =& ReserveModel::getOccupationListByUsername($displayname, $responseModel->get('usergroupid'));
		$this->Template->setChildDataSource('occupationsList', $occupationsList);
		
		/*
		//Populating days of week
		$monday = ReserveModel::getMonday($responseModel->get('date'));
		$mondaytimestamp = strtotime($monday);
		$timestamp = strtotime($responseModel->get('date'));
        for ($i = 1; $i <= 7; $i++) {
			$daysArray[$i]['dayName'] = ucfirst(strftime('%A', $mondaytimestamp));
			// On windows %e doesn't work, so I use %d with abs()
			$daysArray[$i]['dayNumber'] = abs(strftime('%d', $mondaytimestamp));
			$daysArray[$i]['dayTimestamp'] = $mondaytimestamp;
			if ($timestamp == $mondaytimestamp) {
				$daysArray[$i]['color'] = TRUE;
			}
			$mondaytimestamp += 86400;
		}
		$this->Template->setChildDataSource('daysList', new ArrayDataSet($daysArray));
		*/
		
		// Matrix
		$tableTag =& $this->Template->getChild('matrix');
		$tableTag->setAttribute('align','center');$tableTag->setAttribute('border',0);$tableTag->setAttribute('rules','all');$tableTag->setAttribute('class','calendar');$tableTag->setAttribute('cellpadding','5');$tableTag->setAttribute('cellspacing','1');
		$theadTag =& new TagContainerWidget('thead');
		$rowTag =& new TagContainerWidget('tr');
		$headerTag =& new TagContainerWidget('th');$headerTag->setAttribute('nowrap','nowrap');$headerTag->setAttribute('bordercolordark','#339900');$headerTag->setAttribute('bordercolorlight','#333300');		
		$text =& new TextWidget('Turno / PC');
		$headerTag->addChild($text);
		$rowTag->addChild($headerTag);
		
		$computersList =& ReserveModel::getcomputersList();$computersList->reset();
		$i = 0;
		while ($computersList->next()) {
			$i++;
			$headerTag =& new TagContainerWidget('th');$headerTag->setAttribute('nowrap','nowrap');$headerTag->setAttribute('class','title');$headerTag->setAttribute('bordercolordark','#339900');$headerTag->setAttribute('bordercolorlight','#333300');		
			$text =& new TextWidget($computersList->get('hostname'));
			$headerTag->addChild($text);
			$rowTag->addChild($headerTag);
			$computerCount++;
		}
		$theadTag->addChild($rowTag);
		$tableTag->addChild($theadTag);

		$timeline =& ReserveModel::getTimeline($responseModel->get('date'));
		$timeline->reset();
		$tbody =& new TagContainerWidget('tbody');
		while ($timeline->next()) {
			if ($timeline->get('visible') == 'Y') {
				$rowTag =& new TagContainerWidget('tr');

				$bgcolor = ($timeline->get('reservable') != 'Y') ? '#EEEEEE' : '#FFFFFF';
				$rowTag->setAttribute('bgcolor', $bgcolor);			
				
				$headerTag =& new TagContainerWidget('th');$headerTag->setAttribute('nowrap','nowrap');$headerTag->setAttribute('class','title');$headerTag->setAttribute('bordercolordark','#339900');$headerTag->setAttribute('bordercolorlight','#333300');		
				$div =& new TagContainerWidget('div');$div->setAttribute('align','right');
				$text =& new HTMLTextWidget('<a id="'.$timeline->get('time_initial').'"></a>'.date('g:i', strtotime($timeline->get('time_initial'))).' a '.date('g:i A', strtotime($timeline->get('time_final'))));
				$div->addChild($text);
				$headerTag->addChild($div);
				$rowTag->addChild($headerTag);

				if ($timeline->get('reservable') != 'Y') {
					$columnTag =& new TagContainerWidget('td');
					$columnTag->setAttribute('colspan', $computerCount);
					$rowTag->addChild($columnTag);
				} else {
					$findIfTodayIsToReserveThisDatetime = ReserveModel::findIfTodayIsToReserveThisDatetime($timeline->get('date').' '.$timeline->get('time_initial'), $responseModel->get('usergroupid'));
					$computersList =& ReserveModel::getcomputersList();
					$computersList->reset();
					while ($computersList->next()) {
						$rec =& ReserveModel::getOccupationByComputerAndTurn($computersList->get('ip'), $timeline->get('date').' '.$timeline->get('time_initial'));
						if ($rec) {
							$bgcolor = ($rec->get('displayname') == $responseModel->get('username')) ? '#999999' : '#CCCCCC';
							$columnTag =& new TagContainerWidget('td');$columnTag->setAttribute('align', "center");$columnTag->setAttribute('bgcolor', $bgcolor);$columnTag->setAttribute('bordercolordark','#FFFFFF');$columnTag->setAttribute('bordercolorlight','#000000');		
							$text =& new TextWidget($rec->get('displayname'));
							$columnTag->addChild($text);
						} else {
							$columnTag =& new TagContainerWidget('td');$columnTag->setAttribute('align', "center");$columnTag->setAttribute('bordercolordark','#FFFFFF');$columnTag->setAttribute('bordercolorlight','#000000');$columnTag->setAttribute('class','calendar');
							if ($findIfTodayIsToReserveThisDatetime && ($timeline->get('date').' '.$timeline->get('time_initial') >= date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')) + 3600))) {
								$anchorTag =& new TagContainerWidget('a');$anchorTag->setAttribute('href', 'index.php/reservacion/insertar/?ip='.$computersList->get('ip').'&datetime_initial='.$timeline->get('date').' '.$timeline->get('time_initial'));
								$img =& new TagContainerWidget('img');$img->setAttribute('src', "img/reserve.gif");$img->setAttribute('border', "0");$img->setAttribute('alt', "Reservar este turno");
								$anchorTag->addChild($img);
								$columnTag->addChild($anchorTag);
							} else {
								$text =& new HTMLTextWidget('&nbsp;');
								$columnTag->addChild($text);
							}
						}
						$rowTag->addChild($columnTag);
					}
				}
				$tbody->addChild($rowTag);
			}
		}
		$tableTag->addChild($tbody);
		
		$reservableDateList = ReserveModel::getDateReservablesByToday($responseModel->get('usergroupid'));
		$this->Template->setChildDataSource('reservableDateList', $reservableDateList);		
	}
}

class ShowPageController extends PageController {

    function ShowPageController() {
        parent::PageController();
		
		$this->setDefaultView(new ShowView('/reservation/show.html'));		
    }
}
?>