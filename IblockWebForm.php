<?
namespace XSite;

AddEventHandler("iblock", "OnAfterIBlockElementAdd", Array("\XSite\IblockWebForm", "OnAfterIBlockElementAddHandler"));

class IblockWebForm {

	public static function  OnAfterIBlockElementAddHandler($arFields) {
		global $DB;

		$ID = $arFields['ID'];
		$IBLOCK_ID = $arFields['IBLOCK_ID'];
		$event = "IBLOCK_ADD_FORM_{$IBLOCK_ID}";


		$rs = $DB->Query("
			select * 
			from b_event_type
			where EVENT_NAME = '$event'");


		$rsSites = \CSite::GetList($by="sort", $order="desc", Array());
		while ($arSite = $rsSites->Fetch())
		{
		  $arSites[] = $arSite['LID'];
		}

		if ($v = $rs->fetch()) {

			$arSend = self::GetSendArray($ID, $IBLOCK_ID);
			\CEvent::Send($event, $arSites, $arSend);

		}
	}

	public static function GetSendArray($ID, $IBLOCK_ID = false) {

		if (!\CModule::IncludeModule('iblock')) return false;

			$arSend = [];

			$rs = \CIBlockElement::GetList([],["ID"=>$ID,"IBLOCK_ID"=>$IBLOCK_ID],false, false);
			if ($ob = $rs->GetNextElement()) {
				$f = $ob->GetFields();
				foreach ($f as $k=>$v) {
					if ($k[0] == "~") continue;
					$arSend["FIELD_".$k] = $v;
				}
				$f = $ob->GetProperties();
				foreach ($f as $k=>$v) {
					$val = is_array($v['VALUE']) ? join(", ", $v['VALUE']) : $v['VALUE'];
					if ($v['PROPERTY_TYPE'] == "F") {

						if ($val) {

							$file = \CFile::GetFileArray($val);

							$scheme = "http://";
							if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') { $scheme = "https://"; }
							$val = $scheme.$_SERVER['HTTP_HOST'].$file['SRC'];

						} else {

							$val = '-';

						}

						$arSend["PROP_".$k] = $val;
						$arSend["PROP_".$k."_HTML"] = $file['ORIGINAL_NAME'] . ' [<a href="'.$val.'" target="_blank">Скачать</a>]';

					} else {

						$arSend["PROP_".$k] = $val;

					}
				}
			}

		return $arSend;

	}

}