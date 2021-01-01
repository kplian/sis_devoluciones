<?php
/**
*@package pXP
*@file gen-ACTLiquidacion.php
*@author  (admin)
*@date 17-04-2020 01:54:37
*@description Clase que recibe los parametros enviados por la vista para mandar a la capa de Modelo
 HISTORIAL DE MODIFICACIONES:
#ISSUE				FECHA				AUTOR				DESCRIPCION
 #0				17-04-2020 01:54:37								CREACION

*/
include_once(dirname(__FILE__).'/../../lib/lib_modelo/ConexionSqlServer.php');

class ACTLiquidacion extends ACTbase{    
			
	function listarLiquidacion(){
		$this->objParam->defecto('ordenacion','id_liquidacion');

		$this->objParam->defecto('dir_ordenacion','asc');


        if($this->objParam->getParametro('id_liquidacion') != ''){
            $this->objParam->addFiltro("liqui.id_liquidacion = ".$this->objParam->getParametro('id_liquidacion'));
        }
        if($this->objParam->getParametro('estado') != ''){
            $this->objParam->addFiltro("liqui.estado = ''".$this->objParam->getParametro('estado')."'' " );
        }

		if($this->objParam->getParametro('tipoReporte')=='excel_grid' || $this->objParam->getParametro('tipoReporte')=='pdf_grid'){
			$this->objReporte = new Reporte($this->objParam,$this);
			$this->res = $this->objReporte->generarReporteListado('MODLiquidacion','listarLiquidacion');
		} else{
			$this->objFunc=$this->create('MODLiquidacion');
			
			$this->res=$this->objFunc->listarLiquidacion($this->objParam);
		}
		$this->res->imprimirRespuesta($this->res->generarJson());
	}
				
	function insertarLiquidacion(){
		$this->objFunc=$this->create('MODLiquidacion');	
		if($this->objParam->insertar('id_liquidacion')){
			$this->res=$this->objFunc->insertarLiquidacion($this->objParam);			
		} else{			
			$this->res=$this->objFunc->modificarLiquidacion($this->objParam);
		}
		$this->res->imprimirRespuesta($this->res->generarJson());
	}
						
	function eliminarLiquidacion(){
			$this->objFunc=$this->create('MODLiquidacion');	
		$this->res=$this->objFunc->eliminarLiquidacion($this->objParam);
		$this->res->imprimirRespuesta($this->res->generarJson());
	}
	function listarBoleto(){
			$this->objFunc=$this->create('MODLiquidacion');
		$this->res=$this->objFunc->listarBoleto($this->objParam);
		$this->res->imprimirRespuesta($this->res->generarJson());
	}
	function obtenerTramos(){
			$this->objFunc=$this->create('MODLiquidacion');
		$this->res=$this->objFunc->obtenerTramos($this->objParam);
		$this->res->imprimirRespuesta($this->res->generarJson());
	}
	function obtenerTramosSql(){

		$billete = $this->objParam->getParametro('billete');
        $param_conex = array();

        $conexion = new ConexionSqlServer('172.17.110.6', 'SPConnection', 'Passw0rd', 'DBStage');
        $conn = $conexion->conectarSQL();

        //EXECUTE [dbo].[spa_getRoutingTicket] @ticketNumber
        if($conn=='connect') {
            $error = 'connect';
            throw new Exception("connect: La conexión a la bd SQL Server ".$param_conex[1]." ha fallado.");
        }else if($conn=='select_db'){
            $error = 'select_db';
            throw new Exception("select_db: La seleccion de la bd SQL Server ".$param_conex[1]." ha fallado.");
        }else {

            $query_string = "exec DBStage.dbo.spa_getRoutingTicket @ticketNumber= $billete "; // boleto miami 9303852215072

            //$query_string = "select * from AuxBSPVersion";
            //$query_string = utf8_decode("select FlightItinerary from FactTicket where TicketNumber = '9302400056027'");

            $query = @mssql_query($query_string, $conn);
            //$query = @mssql_query(utf8_decode('select * from AuxBSPVersion'), $conn);

            $data = array();

            while ($row = mssql_fetch_array($query, MSSQL_ASSOC)){

                $row["id"] = $row["Origin"] .'-'.$row["Destination"];
                $row["desc"] = $row["Origin"] .'-'.$row["Destination"] . '(' .$row["CouponStatus"] . ')';

                $data[] = $row;
            }


            $send = array(
                "total" => count($row),
                "datos"=> $data
            );
            $send = json_encode($send, true);
            echo $send;
            mssql_free_result($query);
            $conexion->closeSQL();
        }
	}

	function verLiquidacion() {
        $this->objFunc=$this->create('MODLiquidacion');
        $this->res=$this->objFunc->verLiquidacion($this->objParam);
        $this->res->imprimirRespuesta($this->res->generarJson());
    }
	function obtenerLiquidacionCorrelativo() {
        $this->objFunc=$this->create('MODLiquidacion');
        $this->res=$this->objFunc->obtenerLiquidacionCorrelativo($this->objParam);
        $this->res->imprimirRespuesta($this->res->generarJson());
    }


    function getTicketInformationRecursive() {
        $billete = $this->objParam->getParametro('billete');
        $array = array();


        $conexion = new ConexionSqlServer('172.17.110.6', 'SPConnection', 'Passw0rd', 'DBStage');
        $conn = $conexion->conectarSQL();
        //$query_string = "exec DBStage.dbo.fn_getTicketInformation @ticketNumber= 9303852215072 "; // boleto miami 9303852215072
        //$query_string = "Select DBStage.dbo.fn_getTicketInformation('9302404396356') "; // boleto miami 9303852215072
        $query_string = "Select DBStage.dbo.fn_getTicketInformation('$billete') "; // boleto miami 9303852215072

        //$query_string = "select * from AuxBSPVersion";
        //$query_string = utf8_decode("select FlightItinerary from FactTicket where TicketNumber = '9302400056027'");
        @mssql_query('SET CONCAT_NULL_YIELDS_NULL ON');
        @mssql_query('SET ANSI_WARNINGS ON');
        @mssql_query('SET ANSI_PADDING ON');

        $query = @mssql_query($query_string, $conn);
        $row = mssql_fetch_array($query, MSSQL_ASSOC);
        $data_json_string = $row['computed'];
        //var_dump($data_json_string);
        $data_json = json_decode($data_json_string);
        $data = $data_json[0];
        $netAmount = $data ->netAmount;
        $totalAmount = $data->totalAmount;
        $ticketNumber = $data->ticketNumber;


        array_push($array, array('seleccionado' => 'si',
            'billete' => $ticketNumber,
            'monto' => $totalAmount,
            'itinerary' => $data->itinerary,
            'passengerName' => $data->passengerName,
            'currency' => $data->currency,
            'issueOfficeID' => $data->issueOfficeID,
            'netAmount' => $data->netAmount
        ));

        $OriginalTicket = $data->OriginalTicket;
        //var_dump($OriginalTicket);
        while ($OriginalTicket != '') {

            array_push($array, array('seleccionado' => 'si',
                'billete' => $OriginalTicket->ticketNumber,
                'monto' => $OriginalTicket->totalAmount,
                'itinerary' => $OriginalTicket->itinerary,
                'passengerName' => $data->passengerName,
                'currency' => $data->currency,
                'issueOfficeID' => $data->issueOfficeID,
                'netAmount' => $data->netAmount
            ));

            $OriginalTicket = $OriginalTicket->OriginalTicket;
        }



        $send = array(
            "datos" =>  $array,
            "ticket_information" =>  $data,
            "total" => count($array),
        );

        echo json_encode($send);

    }

	function getTicketInformation() {
        $billete = $this->objParam->getParametro('billete');
        $typeReturn = $this->objParam->getParametro('typeReturn');
        if($typeReturn == NULL) {

            /*$conexion = new ConexionSqlServer('172.17.110.6', 'SPConnection', 'Passw0rd', 'DBStage');
            $conn = $conexion->conectarSQL();
            //$query_string = "exec DBStage.dbo.fn_getTicketInformation @ticketNumber= 9303852215072 "; // boleto miami 9303852215072
            $query_string = "Select DBStage.dbo.fn_getTicketInformation('9303852215072') "; // boleto miami 9303852215072

            //$query_string = "select * from AuxBSPVersion";
            //$query_string = utf8_decode("select FlightItinerary from FactTicket where TicketNumber = '9302400056027'");
            @mssql_query('SET CONCAT_NULL_YIELDS_NULL ON');
            @mssql_query('SET ANSI_WARNINGS ON');
            @mssql_query('SET ANSI_PADDING ON');

            $query = @mssql_query($query_string, $conn);
            $row = mssql_fetch_array($query, MSSQL_ASSOC);
            $dataFromSql = json_decode($row['computed']);
            $data = $dataFromSql[0];
            var_dump($dataFromSql);*/

            echo '[{"ticketNumber":"9302401538940  ","pnrCode":"V978TF","transaction":"TKTT","passengerName":"ESPINOZAHERBAS\/DANIEL MARTIN","issueDate":"2020-06-18","issueAgencyCode":"56991314","issueOfficeID":"TJAOB04TR","issueAgent":"9998WS","reserveAgencyCode":"56991314","reserveOfficeID":"TJAOB04TR","ReserveAgent":"9998WS","currency":"BOB","origin":"SRZ","destination":"SRZ","netAmount":284.000000,"totalAmount":369.000000,"itinerary":"VVI - CBB - VVI","fareCalculation":"SRZ OB CBB142.00OB SRZ142.00BOB284.00END","coupon":[{"conjuntionTicketNumber":"9302401538940  ","couponNumber":1,"origin":"VVI","destination":"CBB","flightNumber":"0647 ","fareBasis":"OBOA           ","depatureDate":"28AUG","CouponStatus":"OPEN"},{"conjuntionTicketNumber":"9302401538940  ","couponNumber":2,"origin":"CBB","destination":"VVI","flightNumber":"0650 ","fareBasis":"OBOA           ","depatureDate":"07SEP","CouponStatus":"OPEN"}],"payment":[{"paymentCode":"EXT","paymentDescription":"EXTERNAL PAYMENT FORMS","paymentMethodCode":"BE","paymentMethodDescription":"Banca Electrónica","paymentInstanceCode":"02","paymentInstanceDescription":"Banco Unión","paymentAmount":369.000000,"reference":"EXTBE01\/02000000002050541384\/0620\/KGMEEK"},{"paymentCode":"CA","paymentDescription":"CASH","paymentAmount":0.000000}],"taxes":[{"taxCode":"A7      ","taxAmount":30.000000,"reference":""},{"taxCode":"BO      ","taxAmount":44.000000,"reference":""},{"taxCode":"QM      ","taxAmount":11.000000,"reference":""}]}]';

        } else {
            return '[{"ticketNumber":"9302401538940  ","pnrCode":"V978TF","transaction":"TKTT","passengerName":"ESPINOZAHERBAS\/DANIEL MARTIN","issueDate":"2020-06-18","issueAgencyCode":"56991314","issueOfficeID":"TJAOB04TR","issueAgent":"9998WS","reserveAgencyCode":"56991314","reserveOfficeID":"TJAOB04TR","ReserveAgent":"9998WS","currency":"BOB","origin":"SRZ","destination":"SRZ","netAmount":284.000000,"totalAmount":369.000000,"itinerary":"VVI - CBB - VVI","fareCalculation":"SRZ OB CBB142.00OB SRZ142.00BOB284.00END","coupon":[{"conjuntionTicketNumber":"9302401538940  ","couponNumber":1,"origin":"VVI","destination":"CBB","flightNumber":"0647 ","fareBasis":"OBOA           ","depatureDate":"28AUG","CouponStatus":"OPEN"},{"conjuntionTicketNumber":"9302401538940  ","couponNumber":2,"origin":"CBB","destination":"VVI","flightNumber":"0650 ","fareBasis":"OBOA           ","depatureDate":"07SEP","CouponStatus":"OPEN"}],"payment":[{"paymentCode":"EXT","paymentDescription":"EXTERNAL PAYMENT FORMS","paymentMethodCode":"BE","paymentMethodDescription":"Banca Electrónica","paymentInstanceCode":"02","paymentInstanceDescription":"Banco Unión","paymentAmount":369.000000,"reference":"EXTBE01\/02000000002050541384\/0620\/KGMEEK"},{"paymentCode":"CA","paymentDescription":"CASH","paymentAmount":0.000000}],"taxes":[{"taxCode":"A7      ","taxAmount":30.000000,"reference":""},{"taxCode":"BO      ","taxAmount":44.000000,"reference":""},{"taxCode":"QM      ","taxAmount":11.000000,"reference":""}]}]';

        }

        /*$conexion = new ConexionSqlServer('172.17.110.6', 'SPConnection', 'Passw0rd', 'DBStage');
        $conn = $conexion->conectarSQL();
        $query_string = "exec DBStage.dbo.fn_getTicketInformation @ticketNumber= 9303852215072 "; // boleto miami 9303852215072

        //$query_string = "select * from AuxBSPVersion";
        $query_string = utf8_decode("select FlightItinerary from FactTicket where TicketNumber = '9302400056027'");

        $query = @mssql_query($query_string, $conn);
        $row = mssql_fetch_array($query, MSSQL_ASSOC);*/

    }


    function siguienteEstadoLiquidacion() {
        $this->objFunc=$this->create('MODLiquidacion');
        $this->res=$this->objFunc->siguienteEstadoLiquidacion($this->objParam);
        $this->res->imprimirRespuesta($this->res->generarJson());
    }

    function obtenerJsonPagar() {
        $this->objFunc=$this->create('MODLiquidacion');
        $this->res=$this->objFunc->obtenerJsonPagar($this->objParam);
        $this->res->imprimirRespuesta($this->res->generarJson());
    }

    function listarLiquidacionDetalle() {


        $this->objParam->defecto('ordenacion','id_liquidacion');

        $this->objParam->defecto('dir_ordenacion','asc');




        if($this->objParam->getParametro('tipo') == 'FACCOM'){

            if($this->objParam->getParametro('id_liquidacion') != ''){
                $this->objParam->addFiltro("tl.id_liquidacion = ".$this->objParam->getParametro('id_liquidacion'));
            }
            if($this->objParam->getParametro('estado') != ''){
                $this->objParam->addFiltro("tl.estado = ''".$this->objParam->getParametro('estado')."'' " );
            }

            $this->objFunc=$this->create('MODLiquidacion');
            $this->res=$this->objFunc->listarLiquidacionDetalle($this->objParam);
            $this->res->imprimirRespuesta($this->res->generarJson());
        } else {

            if($this->objParam->getParametro('id_liquidacion') != ''){
                $this->objParam->addFiltro("liqui.id_liquidacion = ".$this->objParam->getParametro('id_liquidacion'));
            }
            if($this->objParam->getParametro('estado') != ''){
                $this->objParam->addFiltro("liqui.estado = ''".$this->objParam->getParametro('estado')."'' " );
            }

            $this->objFunc=$this->create('MODLiquidacion');
            $this->res=$this->objFunc->listarLiquidacion($this->objParam);
            $this->res->imprimirRespuesta($this->res->generarJson());
        }


    }

			
}

?>