<?php

	$content = file_get_contents("php://input");
	$update = json_decode($content, true);

	if(!$update)
	{
	  exit;
	}

	$message = isset($update['message']) ? $update['message'] : "";
	$messageId = isset($message['message_id']) ? $message['message_id'] : "";
	$chatId = isset($message['chat']['id']) ? $message['chat']['id'] : "";
	$firstname = isset($message['chat']['first_name']) ? $message['chat']['first_name'] : "";
	$lastname = isset($message['chat']['last_name']) ? $message['chat']['last_name'] : "";
	$username = isset($message['chat']['username']) ? $message['chat']['username'] : "";
	$date = isset($message['date']) ? $message['date'] : "";
	$text = isset($message['text']) ? $message['text'] : "";
	$text = strtolower($text);
	$response = '';
	$city = '';
    header("Content-Type: application/json");

	//variabili per emoji condizioni meteo;
	$sole = "\xe2\x98\x80\xef\xb8\x8f"; //sereno
	$pioggiaLeggera = "\xe2\x98\x94\xef\xb8\x8f"; //pioggia leggera 
	$pioggiaModerata = "\xe2\x98\x94\xef\xb8\x8f"; //pioggia moderata
	$pocheNuvole = "\xf0\x9f\x8c\xa4"; //nuvole
	$nubiSparse = "\xe2\x9b\x85"; //nubi
	$neve = "\xe2\x9d\x84"; //neve
	$coperto = "\xe2\x98\x81\xef\xb8\x8f"; //coperto
	$foschia = "🌫"; //foschia
	$acquazzone = "⛈"; //acquazzone


	// caso saluto iniziale o comando start
	if(strpos($text, "/start") === 0 || $text=="ciao"){
		$text = trim($text);
		if(substr("$firstname", -1) == "a"){
			$response = "Ciao $firstname, benvenuta! inserisci il nome della tua città ed inviamelo. Per aiuto, o per un altro genere di previsioni, clicca su /help";
		}else{
			$response = "Ciao $firstname, benvenuto! inserisci il nome della tua città ed inviamelo. Per aiuto, o per un altro genere di previsioni, clicca su /help";
		}
	}

	// caso richiesta d' aiuto
	elseif(strpos($text, '/help') !== false){
		$response ="Eccoci $firstname!\r\n \xe2\x9c\x85 Per conoscere il meteo in tempo reale ti basta inviare il nome della tua città. Ad esempio: Roma.\r\n\n \xe2\x9c\x85 Per conoscere il meteo di domani invece dovrai inviarmi il nome della tua città seguito dalla parola domani. Ad esempio: Roma domani.\r\n\n \xe2\x9c\x85 In fine per conoscere il meteo per i successivi 3 giorni della tua città inviami il nome della città unito a 3 giorni. Esempio: Roma 3 giorni.\r\n\n \xe2\x9d\x97 PS: in caso di insicurezza sull' esattezza delle previsioni, aggiungere alla propria località la provincia oppure lo stato. Esempio: Amalfi Salerno oppure Amalfi Italia. Se dovessero persistere dubbi scrivi o clicca /alert.";
	}

	// caso richiesta visione avvertenze
	elseif(strpos($text, '/alert') !== false){
		$response ="Ciao $firstname!\r\nQui voglio metterti a conscenza di alcune anomalie che potresti riscontrare. A volte per paesini per cosi dire più 'nascosti' potrebbero esserci delle variazioni di temperatura, verso il basso per un massimo di 7°C gradi. Questo perchè, i dati a cui attingo sono dati su scala mondiale, quindi non tutte le stazioni meteo sono coperte data la grande mole delle medesime presenti nel 🌍. Questo porta ad approssimare con dei calcoli tra una stazione e l' altra le temperature di una località non coperta. Pertanto $firstname se mai dovessi avere dubbi, il mio consiglio è controllare il meteo riferendosi ad un capoluogo o cittàdina a te vicina di maggior rilievo. Grazie per la comprensione.😁";
	}

	//caso voglia di votare
	elseif(strpos($text, '/votami') !== false){
		$response ="Salve $firstname!\r\nse per te sono stato anche un pochino utile, per supportarmi, se ti va potresti lasciare qualche \xe2\xad\x90\xef\xb8\x8f ed un bel commento qui: https://storebot.me/bot/meteoitalybot Grazie.\xf0\x9f\x98\x98";
	}
	
	//caso città + parola domani quindi meteo domani
	elseif(strpos($text, 'domani') !== false){
		$text = str_replace('domani', '', $text); // toglie la parola domani
		$text = str_replace('.', '', $text); // toglie il punto
		$text = trim($text); // ulteriore pulizia del testo
		$city = $text;
		
		$richiestaMeteo = "http://api.openweathermap.org/data/2.5/forecast/daily?q=$city&units=metric&cnt=2&lang=it&appid=25fc1c956e6e9a25ab68fcb3b7606a73";
	    $rispostaMeteo  = file_get_contents($richiestaMeteo);
	    $jsonobj  = json_decode($rispostaMeteo,true);

	    /*
	    controllo anomalia
	      controllare se la variabile $jsonobj ormai divenuta di tipo array dopo il decode è vuota ci consente di annullare i falsi positivi. Infatti anche in caso di città non esistente il server darà una risposta con codice 200 non un 404. 
	    */
	    if(empty($jsonobj)){ 
			$response = "Per favore, invia un messaggio di testo o un nomd una esistente nel \xf0\x9f\x8c\x8d. Graziee di città quantomeno simile a\xf0\x9f\x98\x98";
		}
        else{

		    $descrizione = $jsonobj['list'][1]['weather'][0]['description'];
		    $temperaturaMax = intval((int)$jsonobj['list'][1]['temp']['max']);
		    $temperaturaMin = intval((int)$jsonobj['list'][1]['temp']['min']);
		    $umid = $jsonobj['list'][1]['humidity'];
		    $vento = intval((((int)$jsonobj['list'][1]['speed'] )* 3.6));
		    $apiCity = $jsonobj["city"]["name"];

		    $condition ="";
		    //switch case per le condizioni con giusta emoji
		    switch ($descrizione) {
	    		case(strpos($descrizione, 'sereno') == true) :
	        		$condition = $sole;
	        	break;
	    		case(strpos($descrizione, 'sparse') == true) :
	        		$condition = $nubiSparse;
	        	break;
	        	case(strpos($descrizione, 'nuvole') == true) :
	        		$condition = $pocheNuvole;
	        	break;
	        	case(strpos($descrizione, 'leggera') == true) :
	        		$condition = $pioggiaLeggera;
	        	break;
	        	case(strpos($descrizione, 'moderata') == true) :
	        		$condition = $pioggiaModerata;
	        	break;
	        	case(strpos($descrizione, 'neve') == true) :
	        		$condition = $neve;
	        	break;
	        	case(strpos($descrizione, 'coperto') == true) :
	        		$condition = $coperto;
	        	break;
	        	case(strpos($descrizione, 'foschia') == true) :
	        		$condition = $foschia;
	        	break;
	        	case(strpos($descrizione, 'acquazzone') == true) :
	        		$condition = $acquazzone;
	        	break;
			}

		    $response = "Domani a \xf0\x9f\x93\x8d $apiCity il meteo dice:\r\n $condition $descrizione\r\n \xf0\x9f\x8c\xa1 temperatura max: $temperaturaMax °C gradi\r\n \xf0\x9f\x8c\xa1 temperatura min: $temperaturaMin °C gradi\r\n \xf0\x9f\x8d\x83 vento a: $vento km/h\r\n \xf0\x9f\x92\xa6 umidità: $umid %";
		}
	
	//caso meteo 3 giorni chiamata: Sora 3 giorni o sora meteo per 3 giorni
	}elseif(strpos($text, 'giorni') !== false){
		$text = str_replace('giorni', '', $text); 
		$text = str_replace('3', '', $text); 
		$text = str_replace('.', '', $text); 
		$text = trim($text);
		$city = $text;
		
		$richiestaMeteo = "http://api.openweathermap.org/data/2.5/forecast/daily?q=$city&units=metric&cnt=4&lang=it&appid=25fc1c956e6e9a25ab68fcb3b7606a73";
	    $rispostaMeteo  = file_get_contents($richiestaMeteo);
	    $jsonobj  = json_decode($rispostaMeteo,true);

	    if(empty($jsonobj)){
			$response = "Per favore, invia un messaggio di testo o un nome di città quantomeno simile ad una esistente nel \xf0\x9f\x8c\x8d. Grazie\xf0\x9f\x98\x98";
		}
        else{

            //domani
            $descrizione = $jsonobj['list'][1]['weather'][0]['description'];
            $temperaturaMax = intval((int)$jsonobj['list'][1]['temp']['max']);
            $temperaturaMin = intval((int)$jsonobj['list'][1]['temp']['min']);
            $umid = $jsonobj['list'][1]['humidity'];
            $vento = intval((((int)$jsonobj['list'][1]['speed']) * 3.6));
            $condition ="";

            //per gestire variabili nello switch case  a 3 giorni
            switch ($descrizione) {
                case(strpos($descrizione, 'sereno') == true) :
                    $condition = $sole;
                break;
                case(strpos($descrizione, 'nubi') == true) :
                    $condition = $nubiSparse;
                break;
                case(strpos($descrizione, 'nuvole') == true) :
                    $condition = $pocheNuvole;
                break;
                case(strpos($descrizione, 'leggera') == true) :
                    $condition = $pioggiaLeggera;
                break;
                case(strpos($descrizione, 'moderata') == true) :
                    $condition = $pioggiaModerata;
                break;
                case(strpos($descrizione, 'neve') == true) :
                    $condition = $neve;
                break;
                case(strpos($descrizione, 'coperto') == true) :
                    $condition = $coperto;
                break;
                case(strpos($descrizione, 'foschia') == true) :
	        		$condition = $foschia;
	        	break;
	        	case(strpos($descrizione, 'acquazzone') == true) :
	        		$condition = $acquazzone;
	        	break;

            }
            
            //dopodomani
            $data1 = $jsonobj['list'][2]['dt'];
            $data1 = gmdate("Y-m-d", $data1);// trasforma data unix format in formato voluto
            $descrizione1 = $jsonobj['list'][2]['weather'][0]['description'];
            $temperaturaMax1 = intval((int)$jsonobj['list'][2]['temp']['max']);
            $temperaturaMin1 = intval((int)$jsonobj['list'][2]['temp']['min']);
            $umid1 = $jsonobj['list'][2]['humidity'];
            $vento1 = intval((((int)$jsonobj['list'][2]['speed']) * 3.6));
            $condition1 ="";

            //per gestire variabili nello switch case  a 3 giorni
            switch ($descrizione1) {
                case(strpos($descrizione1, 'sereno') == true) :
                    $condition1 = $sole;
                break;
                case(strpos($descrizione1, 'nubi') == true) :
                    $condition1 = $nubiSparse;
                break;
                case(strpos($descrizione1, 'nuvole') == true) :
                    $condition1 = $pocheNuvole;
                break;
                case(strpos($descrizione1, 'leggera') == true) :
                    $condition1 = $pioggiaLeggera;
                break;
                case(strpos($descrizione1, 'moderata') == true) :
                    $condition1 = $pioggiaModerata;
                break;
                case(strpos($descrizione1, 'neve') == true) :
                    $condition1 = $neve;
                break;
                case(strpos($descrizione1, 'coperto') == true) :
                    $condition1 = $coperto;
                break;
                case(strpos($descrizione1, 'foschia') == true) :
	        		$condition1 = $foschia;
	        	break;
	        	case(strpos($descrizione1, 'acquazzone') == true) :
	        		$condition1 = $acquazzone;
	        	break;
            }
            
            //dopodomani +1
            $data2 = $jsonobj['list'][3]['dt'];
            $data2 = gmdate("Y-m-d", $data2);
            $descrizione2 = $jsonobj['list'][3]['weather'][0]['description'];
            $temperaturaMax2 = intval((int)$jsonobj['list'][3]['temp']['max']);
            $temperaturaMin2 = intval((int)$jsonobj['list'][3]['temp']['min']);
            $umid2 = $jsonobj['list'][3]['humidity'];
            $vento2 = intval((((int)$jsonobj['list'][3]['speed']) * 3.6));
            $apiCity = $jsonobj["city"]["name"];
            $condition2 = "";

            //per gestire variabili nello switch case  a 3 giorni
            switch ($descrizione2) {
                case(strpos($descrizione2, 'sereno') == true) :
                    $condition2 = $sole;
                break;
                case(strpos($descrizione2, 'nubi') == true) :
                    $condition2 = $nubiSparse;
                break;
                case(strpos($descrizione2, 'nuvole') == true) :
                    $condition2 = $pocheNuvole;
                break;
                case(strpos($descrizione2, 'leggera') == true) :
                    $condition2 = $pioggiaLeggera;
                break;
                case(strpos($descrizione2, 'moderata') == true) :
                    $condition2 = $pioggiaModerata;
                break;
                case(strpos($descrizione2, 'neve') == true) :
                    $condition2 = $neve;
                break;
                case(strpos($descrizione2, 'coperto') == true) :
                    $condition2 = $coperto;
                break;
                case(strpos($descrizione2, 'foschia') == true) :
	        		$condition2 = $foschia;
	        	break;
	        	case(strpos($descrizione2, 'acquazzone') == true) :
	        		$condition2 = $acquazzone;
	        	break;
            }
        }
            $response = "Domani a \xf0\x9f\x93\x8d $apiCity il meteo dice:\r\n $condition $descrizione\r\n \xf0\x9f\x8c\xa1 temperatura max: $temperaturaMax °C gradi\r\n \xf0\x9f\x8c\xa1 temperatura min: $temperaturaMin °C gradi\r\n \xf0\x9f\x8d\x83 vento a: $vento km/h\r\n \xf0\x9f\x92\xa6 umidità: $umid %\r\n\r\nIl $data1 a \xf0\x9f\x93\x8d $apiCity il meteo dice:\r\n $condition1 $descrizione1\r\n \xf0\x9f\x8c\xa1 temperatura max: $temperaturaMax1 °C gradi\r\n \xf0\x9f\x8c\xa1 temperatura min: $temperaturaMin1 °C gradi\r\n \xf0\x9f\x8d\x83 vento a: $vento1 km/h\r\n \xf0\x9f\x92\xa6 umidità: $umid1 %\r\n\r\nIl $data2 a \xf0\x9f\x93\x8d $apiCity il meteo dice:\r\n $condition2 $descrizione2\r\n \xf0\x9f\x8c\xa1 temperatura max: $temperaturaMax2 °C gradi\r\n \xf0\x9f\x8c\xa1 temperatura min di: $temperaturaMin2 °C gradi\r\n \xf0\x9f\x8d\x83 vento a: $vento2 km/h\r\n \xf0\x9f\x92\xa6 umidità: $umid2 %";
		

	//caso inserimento nome di una città quindi meteo attuale
	}elseif(strpos($text, 'domani') == false){
		$text = trim($text);
		$text = str_replace('.', '', $text); 
		$city = $text;
		$richiestaMeteo = "http://api.openweathermap.org/data/2.5/weather?APPID=25fc1c956e6e9a25ab68fcb3b7606a73&q=$city&lang=it&units=metric";
	    $rispostaMeteo  = file_get_contents($richiestaMeteo);
	    $jsonobj  = json_decode($rispostaMeteo,true);

	    if(empty($jsonobj)){
			$response = "Per favore, invia un messaggio di testo o un nome di città quantomeno simile ad una esistente nel \xf0\x9f\x8c\x8d. Grazie\xf0\x9f\x98\x98";
		}
		else{

			$descrizione = $jsonobj['weather'][0]['description'];
		    $temperatura = intval((int)$jsonobj["main"]["temp"]);
		    $vento = intval((((int)$jsonobj['wind']['speed']) * 3.6));
		    $umid = $jsonobj["main"]["humidity"];
		    $apiCity = $jsonobj["name"];
			$condition ="";

		    //switch case per le condizioni meteo con giusta emoji
		    switch ($descrizione) {
	    		case(strpos($descrizione, 'sereno') == true) :
	        		$condition = $sole;
	        	break;
	    		case(strpos($descrizione, 'sparse') == true) :
	        		$condition = $nubiSparse;
	        	break;
	        	case(strpos($descrizione, 'nuvole') == true) :
	        		$condition = $pocheNuvole;
	        	break;
	        	case(strpos($descrizione, 'leggera') == true) :
	        		$condition = $pioggiaLeggera;
	        	break;
	        	case(strpos($descrizione, 'moderata') == true) :
	        		$condition = $pioggiaModerata;
	        	break;
	        	case(strpos($descrizione, 'neve') == true) :
	        		$condition = $neve;
	        	break;
	        	case(strpos($descrizione, 'coperto') == true) :
	        		$condition = $coperto;
	        	break;
	        	case(strpos($descrizione, 'foschia') == true) :
	        		$condition = $foschia;
	        	break;
	        	case(strpos($descrizione, 'acquazzone') == true) :
	        		$condition = $acquazzone;
	        	break;
			}

		    $response = "La condizione meteo attuale a \xf0\x9f\x93\x8d $apiCity è:\r\n $condition $descrizione\r\n \xf0\x9f\x8c\xa1 temperatura di: $temperatura °C gradi\r\n \xf0\x9f\x8d\x83 vento a: $vento km/h\r\n \xf0\x9f\x92\xa6 umidità: $umid %";
		}
	 


	
	$parameters = array('chat_id' => $chatId, "text" => $response);
	$parameters["method"] = "sendMessage"; //invio messaggio dopo specifica parametri
	echo json_encode($parameters); // codifica messaggio in json
}
