<?php

/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;
$idServidorPesquisado = null;

# Configuração
include ("../grhSistema/_config.php");

# Conecta ao Banco de Dados
$pessoal = new Pessoal();
$intra = new Intra();
$licencaPremio = new LicencaPremio();

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {

    # Calcula as datas padrão
    $dtInicial = $licencaPremio->get_dataProximoPeriodo($idServidorPesquisado);

    if (empty($dtInicial)) {
        $dtFinal = null;
        $dtFinal2 = null;
        $dtFinal3 = null;
        $dtFinal4 = null;
    } else {
        $dtFinal = addDias($dtInicial, 1825, false);

        $dtInicial2 = addDias($dtFinal, 1, false);
        $dtFinal2 = addDias($dtInicial2, 1825, false);

        $dtInicial3 = addDias($dtFinal2, 1, false);
        $dtFinal3 = addDias($dtInicial3, 1825, false);

        $dtInicial4 = addDias($dtFinal3, 1, false);
        $dtFinal4 = addDias($dtInicial4, 1825, false);
    }

    # Pega os parâmetros
    $parametroMeses = post('parametroMeses', get_session('parametroMeses', 3));
    $parametroTipo = post('parametroTipo', get_session('parametroTipo', "Concessão"));

    $parametroPublicacaoData = post('parametroPublicacaoData', get_session('parametroPublicacaoData'));
    $parametroPublicacaoPag = post('parametroPublicacaoPag', get_session('parametroPublicacaoPag'));

    $parametroDtInicial = post('parametroDtInicial', get_session('parametroDtInicial', date_to_bd($dtInicial)));
    $parametroDtFinal = post('parametroDtFinal', get_session('parametroDtFinal', date_to_bd($dtFinal)));

    $parametroDtInicial2 = post('parametroDtInicial2', get_session('parametroDtInicial2', date_to_bd($dtInicial2)));
    $parametroDtFinal2 = post('parametroDtFinal2', get_session('parametroDtFinal2', date_to_bd($dtFinal2)));

    $parametroDtInicial3 = post('parametroDtInicial3', get_session('parametroDtInicial3', date_to_bd($dtInicial3)));
    $parametroDtFinal3 = post('parametroDtFinal3', get_session('parametroDtFinal3', date_to_bd($dtFinal3)));

    $parametroDtInicial4 = post('parametroDtInicial4', get_session('parametroDtInicial4', date_to_bd($dtInicial4)));
    $parametroDtFinal4 = post('parametroDtFinal4', get_session('parametroDtFinal4', date_to_bd($dtFinal4)));

    $parametroPreenchido = post('parametroPreenchido', get_session('parametroPreenchido',$intra->get_nomeUsuario($idUsuario)));
    $parametroAcordo = post('parametroAcordo', get_session('parametroAcordo',$pessoal->get_nome($pessoal->get_gerente(66))));

    # Verifica a fase do programa
    $fase = get('fase');

    # Joga os parâmetros par as sessions
    set_session('parametroMeses', $parametroMeses);
    set_session('parametroTipo', $parametroTipo);
    
    set_session('parametroPublicacaoData', $parametroPublicacaoData);
    set_session('parametroPublicacaoPag', $parametroPublicacaoPag);

    set_session('parametroDtInicial', $parametroDtInicial);
    set_session('parametroDtFinal', $parametroDtFinal);

    set_session('parametroDtInicial2', $parametroDtInicial2);
    set_session('parametroDtFinal2', $parametroDtFinal2);

    set_session('parametroDtInicial3', $parametroDtInicial3);
    set_session('parametroDtFinal3', $parametroDtFinal3);

    set_session('parametroDtInicial4', $parametroDtInicial4);
    set_session('parametroDtFinal4', $parametroDtFinal4);

    set_session('parametroPreenchido', $parametroPreenchido);
    set_session('parametroAcordo', $parametroAcordo);

    # Rotina em Jscript
    $script = '<script type="text/javascript" language="javascript">
        
            $(document).ready(function(){
            
                var r = $("#parametroTipo option:selected").val();
                if(r == "Concessão") {
                        $("#parametroPublicacaoData").hide();
                        $("#labelparametroPublicacaoData").hide();
                        $("#parametroPublicacaoPag").hide();
                        $("#labelparametroPublicacaoPag").hide();
                    }
                    
                if(r == "Recontagem") {
                        $("#parametroPublicacaoData").show();
                        $("#labelparametroPublicacaoData").show();
                        $("#parametroPublicacaoPag").show();
                        $("#labelparametroPublicacaoPag").show();
                    } 
                    
                // Quando muda os meses
                $("#parametroTipo").change(function(){
                
                var r = $("#parametroTipo option:selected").val();
                if(r == "Concessão") {
                        $("#parametroPublicacaoData").hide();
                        $("#labelparametroPublicacaoData").hide();
                        $("#parametroPublicacaoPag").hide();
                        $("#labelparametroPublicacaoPag").hide();
                    }
                    
                if(r == "Recontagem") {
                        $("#parametroPublicacaoData").show();
                        $("#labelparametroPublicacaoData").show();
                        $("#parametroPublicacaoPag").show();
                        $("#labelparametroPublicacaoPag").show();
                    } 
                });               
            
                var m = $("#parametroMeses option:selected").val();

                    if(m == 3) {
                        $("#parametroDtInicial").show();
                        $("#labelparametroDtInicial").show();
                        $("#parametroDtFinal").show();
                        $("#labelparametroDtFinal").show();
                        
                        $("#parametroDtInicial2").hide();
                        $("#labelparametroDtInicial2").hide();
                        $("#parametroDtFinal2").hide();
                        $("#labelparametroDtFinal2").hide();
                        
                        $("#parametroDtInicial3").hide();
                        $("#labelparametroDtInicial3").hide();
                        $("#parametroDtFinal3").hide();
                        $("#labelparametroDtFinal3").hide();
                        
                        $("#parametroDtInicial4").hide();
                        $("#labelparametroDtInicial4").hide();
                        $("#parametroDtFinal4").hide();
                        $("#labelparametroDtFinal4").hide();
                    }
                    
                    if(m == 6) {
                        $("#parametroDtInicial").show();
                        $("#labelparametroDtInicial").show();
                        $("#parametroDtFinal").show();
                        $("#labelparametroDtFinal").show();
                        
                        $("#parametroDtInicial2").show();
                        $("#labelparametroDtInicial2").show();
                        $("#parametroDtFinal2").show();
                        $("#labelparametroDtFinal2").show();
                        
                        $("#parametroDtInicial3").hide();
                        $("#labelparametroDtInicial3").hide();
                        $("#parametroDtFinal3").hide();
                        $("#labelparametroDtFinal3").hide();
                        
                        $("#parametroDtInicial4").hide();
                        $("#labelparametroDtInicial4").hide();
                        $("#parametroDtFinal4").hide();
                        $("#labelparametroDtFinal4").hide();
                    }
                    
                    if(m == 9) {
                        $("#parametroDtInicial").show();
                        $("#labelparametroDtInicial").show();
                        $("#parametroDtFinal").show();
                        $("#labelparametroDtFinal").show();
                        
                        $("#parametroDtInicial2").show();
                        $("#labelparametroDtInicial2").show();
                        $("#parametroDtFinal2").show();
                        $("#labelparametroDtFinal2").show();
                        
                        $("#parametroDtInicial3").show();
                        $("#labelparametroDtInicial3").show();
                        $("#parametroDtFinal3").show();
                        $("#labelparametroDtFinal3").show();
                        
                        $("#parametroDtInicial4").hide();
                        $("#labelparametroDtInicial4").hide();
                        $("#parametroDtFinal4").hide();
                        $("#labelparametroDtFinal4").hide();
                    }
                    
                    if(m == 12) {
                        $("#parametroDtInicial").show();
                        $("#labelparametroDtInicial").show();
                        $("#parametroDtFinal").show();
                        $("#labelparametroDtFinal").show();
                        
                        $("#parametroDtInicial2").show();
                        $("#labelparametroDtInicial2").show();
                        $("#parametroDtFinal2").show();
                        $("#labelparametroDtFinal2").show();
                        
                        $("#parametroDtInicial3").show();
                        $("#labelparametroDtInicial3").show();
                        $("#parametroDtFinal3").show();
                        $("#labelparametroDtFinal3").show();
                        
                        $("#parametroDtInicial4").show();
                        $("#labelparametroDtInicial4").show();
                        $("#parametroDtFinal4").show();
                        $("#labelparametroDtFinal4").show();
                    }
            
                // Quando muda os meses
                $("#parametroMeses").change(function(){
                
                    var m = $("#parametroMeses option:selected").val();
                    if(m == 3) {
                        $("#parametroDtInicial").show();
                        $("#labelparametroDtInicial").show();
                        $("#parametroDtFinal").show();
                        $("#labelparametroDtFinal").show();
                        
                        $("#parametroDtInicial2").hide();
                        $("#labelparametroDtInicial2").hide();
                        $("#parametroDtFinal2").hide();
                        $("#labelparametroDtFinal2").hide();
                        
                        $("#parametroDtInicial3").hide();
                        $("#labelparametroDtInicial3").hide();
                        $("#parametroDtFinal3").hide();
                        $("#labelparametroDtFinal3").hide();
                        
                        $("#parametroDtInicial4").hide();
                        $("#labelparametroDtInicial4").hide();
                        $("#parametroDtFinal4").hide();
                        $("#labelparametroDtFinal4").hide();
                    }
                    
                    if(m == 6) {
                        $("#parametroDtInicial").show();
                        $("#labelparametroDtInicial").show();
                        $("#parametroDtFinal").show();
                        $("#labelparametroDtFinal").show();
                        
                        $("#parametroDtInicial2").show();
                        $("#labelparametroDtInicial2").show();
                        $("#parametroDtFinal2").show();
                        $("#labelparametroDtFinal2").show();
                        
                        $("#parametroDtInicial3").hide();
                        $("#labelparametroDtInicial3").hide();
                        $("#parametroDtFinal3").hide();
                        $("#labelparametroDtFinal3").hide();
                        
                        $("#parametroDtInicial4").hide();
                        $("#labelparametroDtInicial4").hide();
                        $("#parametroDtFinal4").hide();
                        $("#labelparametroDtFinal4").hide();
                    }
                    
                    if(m == 9) {
                        $("#parametroDtInicial").show();
                        $("#labelparametroDtInicial").show();
                        $("#parametroDtFinal").show();
                        $("#labelparametroDtFinal").show();
                        
                        $("#parametroDtInicial2").show();
                        $("#labelparametroDtInicial2").show();
                        $("#parametroDtFinal2").show();
                        $("#labelparametroDtFinal2").show();
                        
                        $("#parametroDtInicial3").show();
                        $("#labelparametroDtInicial3").show();
                        $("#parametroDtFinal3").show();
                        $("#labelparametroDtFinal3").show();
                        
                        $("#parametroDtInicial4").hide();
                        $("#labelparametroDtInicial4").hide();
                        $("#parametroDtFinal4").hide();
                        $("#labelparametroDtFinal4").hide();
                    }
                    
                    if(m == 12) {
                        $("#parametroDtInicial").show();
                        $("#labelparametroDtInicial").show();
                        $("#parametroDtFinal").show();
                        $("#labelparametroDtFinal").show();
                        
                        $("#parametroDtInicial2").show();
                        $("#labelparametroDtInicial2").show();
                        $("#parametroDtFinal2").show();
                        $("#labelparametroDtFinal2").show();
                        
                        $("#parametroDtInicial3").show();
                        $("#labelparametroDtInicial3").show();
                        $("#parametroDtFinal3").show();
                        $("#labelparametroDtFinal3").show();
                        
                        $("#parametroDtInicial4").show();
                        $("#labelparametroDtInicial4").show();
                        $("#parametroDtFinal4").show();
                        $("#labelparametroDtFinal4").show();
                    }
                    
                 });
                
                  
                // Quando muda a data Inicial
                $("#parametroDtInicial").change(function(){
                                       
                    var numDias = 1825 + 1;
                    
                    // dtFinal
                    var dt1 = $("#parametroDtInicial").val();
                    data1 = new Date(dt1);
                    data2 = new Date(data1.getTime() + (numDias * 24 * 60 * 60 * 1000));                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");            
                    $("#parametroDtFinal").val(formatado);
                    
                    // dtInicial2
                    var dt2 = $("#parametroDtFinal").val();
                    data1 = new Date(dt2);
                    data2 = new Date(data1.getTime() + (2 * 24 * 60 * 60 * 1000));                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");            
                    $("#parametroDtInicial2").val(formatado); 
                    
                    // dtFinal2
                    var dt3 = $("#parametroDtInicial2").val();
                    data1 = new Date(dt3);
                    data2 = new Date(data1.getTime() + (numDias * 24 * 60 * 60 * 1000));                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");            
                    $("#parametroDtFinal2").val(formatado);
                    
                    // dtInicial3
                    var dt4 = $("#parametroDtFinal2").val();
                    data1 = new Date(dt4);
                    data2 = new Date(data1.getTime() + (2 * 24 * 60 * 60 * 1000));                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");            
                    $("#parametroDtInicial3").val(formatado); 
                    
                    // dtFinal3
                    var dt5 = $("#parametroDtInicial3").val();
                    data1 = new Date(dt5);
                    data2 = new Date(data1.getTime() + (numDias * 24 * 60 * 60 * 1000));                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");            
                    $("#parametroDtFinal3").val(formatado);
                    
                    // dtInicial4
                    var dt6 = $("#parametroDtFinal3").val();
                    data1 = new Date(dt6);
                    data2 = new Date(data1.getTime() + (2 * 24 * 60 * 60 * 1000));                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");            
                    $("#parametroDtInicial4").val(formatado); 
                    
                    // dtFinal4
                    var dt7 = $("#parametroDtInicial4").val();
                    data1 = new Date(dt7);
                    data2 = new Date(data1.getTime() + (numDias * 24 * 60 * 60 * 1000));                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");            
                    $("#parametroDtFinal4").val(formatado);
                });
                
                // Quando muda a data Final
                $("#parametroDtFinal").change(function(){
                                       
                    var numDias = 1825 + 1;
                    
                    // dtInicial2
                    var dt2 = $("#parametroDtFinal").val();
                    data1 = new Date(dt2);
                    data2 = new Date(data1.getTime() + (2 * 24 * 60 * 60 * 1000));                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");            
                    $("#parametroDtInicial2").val(formatado); 
                    
                    // dtFinal2
                    var dt3 = $("#parametroDtInicial2").val();
                    data1 = new Date(dt3);
                    data2 = new Date(data1.getTime() + (numDias * 24 * 60 * 60 * 1000));                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");            
                    $("#parametroDtFinal2").val(formatado);
                    
                    // dtInicial3
                    var dt4 = $("#parametroDtFinal2").val();
                    data1 = new Date(dt4);
                    data2 = new Date(data1.getTime() + (2 * 24 * 60 * 60 * 1000));                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");            
                    $("#parametroDtInicial3").val(formatado); 
                    
                    // dtFinal3
                    var dt5 = $("#parametroDtInicial3").val();
                    data1 = new Date(dt5);
                    data2 = new Date(data1.getTime() + (numDias * 24 * 60 * 60 * 1000));                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");            
                    $("#parametroDtFinal3").val(formatado);
                    
                    // dtInicial4
                    var dt6 = $("#parametroDtFinal3").val();
                    data1 = new Date(dt6);
                    data2 = new Date(data1.getTime() + (2 * 24 * 60 * 60 * 1000));                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");            
                    $("#parametroDtInicial4").val(formatado); 
                    
                    // dtFinal4
                    var dt7 = $("#parametroDtInicial4").val();
                    data1 = new Date(dt7);
                    data2 = new Date(data1.getTime() + (numDias * 24 * 60 * 60 * 1000));                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");            
                    $("#parametroDtFinal4").val(formatado);
                });
                
                // Quando muda a data inicial2
                $("#parametroDtInicial2").change(function(){
                                       
                    var numDias = 1825 + 1;
                    
                    // dtFinal2
                    var dt3 = $("#parametroDtInicial2").val();
                    data1 = new Date(dt3);
                    data2 = new Date(data1.getTime() + (numDias * 24 * 60 * 60 * 1000));                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");            
                    $("#parametroDtFinal2").val(formatado);
                    
                    // dtInicial3
                    var dt4 = $("#parametroDtFinal2").val();
                    data1 = new Date(dt4);
                    data2 = new Date(data1.getTime() + (2 * 24 * 60 * 60 * 1000));                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");            
                    $("#parametroDtInicial3").val(formatado); 
                    
                    // dtFinal3
                    var dt5 = $("#parametroDtInicial3").val();
                    data1 = new Date(dt5);
                    data2 = new Date(data1.getTime() + (numDias * 24 * 60 * 60 * 1000));                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");            
                    $("#parametroDtFinal3").val(formatado);
                    
                    // dtInicial4
                    var dt6 = $("#parametroDtFinal3").val();
                    data1 = new Date(dt6);
                    data2 = new Date(data1.getTime() + (2 * 24 * 60 * 60 * 1000));                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");            
                    $("#parametroDtInicial4").val(formatado); 
                    
                    // dtFinal4
                    var dt7 = $("#parametroDtInicial4").val();
                    data1 = new Date(dt7);
                    data2 = new Date(data1.getTime() + (numDias * 24 * 60 * 60 * 1000));                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");            
                    $("#parametroDtFinal4").val(formatado);
                });
                
                // Quando muda a data final2
                $("#parametroDtFinal2").change(function(){
                                       
                    var numDias = 1825 + 1;
                    
                    // dtInicial3
                    var dt4 = $("#parametroDtFinal2").val();
                    data1 = new Date(dt4);
                    data2 = new Date(data1.getTime() + (2 * 24 * 60 * 60 * 1000));                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");            
                    $("#parametroDtInicial3").val(formatado); 
                    
                    // dtFinal3
                    var dt5 = $("#parametroDtInicial3").val();
                    data1 = new Date(dt5);
                    data2 = new Date(data1.getTime() + (numDias * 24 * 60 * 60 * 1000));                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");            
                    $("#parametroDtFinal3").val(formatado);
                    
                    // dtInicial4
                    var dt6 = $("#parametroDtFinal3").val();
                    data1 = new Date(dt6);
                    data2 = new Date(data1.getTime() + (2 * 24 * 60 * 60 * 1000));                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");            
                    $("#parametroDtInicial4").val(formatado); 
                    
                    // dtFinal4
                    var dt7 = $("#parametroDtInicial4").val();
                    data1 = new Date(dt7);
                    data2 = new Date(data1.getTime() + (numDias * 24 * 60 * 60 * 1000));                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");            
                    $("#parametroDtFinal4").val(formatado);
                });
                
                // Quando muda a data inicial3
                $("#parametroDtInicial3").change(function(){
                                       
                    var numDias = 1825 + 1;
                    
                    // dtFinal3
                    var dt5 = $("#parametroDtInicial3").val();
                    data1 = new Date(dt5);
                    data2 = new Date(data1.getTime() + (numDias * 24 * 60 * 60 * 1000));                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");            
                    $("#parametroDtFinal3").val(formatado);
                    
                    // dtInicial4
                    var dt6 = $("#parametroDtFinal3").val();
                    data1 = new Date(dt6);
                    data2 = new Date(data1.getTime() + (2 * 24 * 60 * 60 * 1000));                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");            
                    $("#parametroDtInicial4").val(formatado); 
                    
                    // dtFinal4
                    var dt7 = $("#parametroDtInicial4").val();
                    data1 = new Date(dt7);
                    data2 = new Date(data1.getTime() + (numDias * 24 * 60 * 60 * 1000));                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");            
                    $("#parametroDtFinal4").val(formatado);
                });
                
                // Quando muda a data final3
                $("#parametroDtFinal3").change(function(){
                                       
                    var numDias = 1825 + 1;
                    
                    // dtInicial4
                    var dt6 = $("#parametroDtFinal3").val();
                    data1 = new Date(dt6);
                    data2 = new Date(data1.getTime() + (2 * 24 * 60 * 60 * 1000));                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");            
                    $("#parametroDtInicial4").val(formatado); 
                    
                    // dtFinal4
                    var dt7 = $("#parametroDtInicial4").val();
                    data1 = new Date(dt7);
                    data2 = new Date(data1.getTime() + (numDias * 24 * 60 * 60 * 1000));                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");            
                    $("#parametroDtFinal4").val(formatado);
                });
                
                // Quando muda a data inicial4
                $("#parametroDtInicial4").change(function(){
                                       
                    var numDias = 1825 + 1;
                    
                    // dtFinal4
                    var dt7 = $("#parametroDtInicial4").val();
                    data1 = new Date(dt7);
                    data2 = new Date(data1.getTime() + (numDias * 24 * 60 * 60 * 1000));                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");            
                    $("#parametroDtFinal4").val(formatado);
                });
                
            });
        </script>';

    # Começa uma nova página
    $page = new Page();
    if ($fase == "") {
        $page->set_jscript($script);
    }
    $page->iniciaPagina();

    # Limita a página
    $grid = new Grid();
    $grid->abreColuna(12);

    ###

    switch ($fase) {
        case "":
            # Título
            titulo("Certidão para Licença Prêmio");
            br();

            # Pega os dados da combo preenchido e de acordo
            $select = 'SELECT tbpessoa.nome, tbpessoa.nome
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND tbhistlot.lotacao = 66
                     ORDER BY tbpessoa.nome asc';

            $relacao = $pessoal->select($select);
            array_unshift($relacao, [null, null]);

            # Formulário de Pesquisa
            $form = new Form("?fase=aguarde");

            $controle = new Input('parametroTipo', 'combo', 'Tipo da Certidão:', 1);
            $controle->set_size(8);
            $controle->set_title('Tipo da Certidão');
            $controle->set_valor($parametroTipo);
            $controle->set_array(["Concessão", "Recontagem"]);
            $controle->set_linha(1);
            $controle->set_col(4);
            $controle->set_autofocus(true);
            $form->add_item($controle);

            $controle = new Input('parametroMeses', 'combo', 'Mes(es):', 1);
            $controle->set_size(8);
            $controle->set_title('Meses que Tem Direito');
            $controle->set_valor($parametroMeses);
            $controle->set_array([3, 6, 9, 12]);
            $controle->set_linha(2);
            $controle->set_col(4);
            $controle->set_autofocus(true);
            $form->add_item($controle);

            ###

            $controle = new Input('parametroDtInicial', 'data', 'Data Inicial do Período:', 1);
            $controle->set_size(20);
            $controle->set_title('Informe a data inicial do período');
            $controle->set_valor($parametroDtInicial);
            $controle->set_linha(3);
            $controle->set_col(6);
            $form->add_item($controle);

            $controle = new Input('parametroDtFinal', 'data', 'Data Final do Período:', 1);
            $controle->set_size(20);
            $controle->set_title('Informe a data final do período');
            $controle->set_valor($parametroDtFinal);
            $controle->set_linha(3);
            $controle->set_col(6);
            $form->add_item($controle);

            ###

            $controle = new Input('parametroDtInicial2', 'data', 'Data Inicial do Período 2:', 1);
            $controle->set_size(20);
            $controle->set_title('Informe a data inicial do período 2');
            $controle->set_valor($parametroDtInicial2);
            $controle->set_linha(3);
            $controle->set_col(6);
            $form->add_item($controle);

            $controle = new Input('parametroDtFinal2', 'data', 'Data Final do Período 2:', 1);
            $controle->set_size(20);
            $controle->set_title('Informe a data final do período 2');
            $controle->set_valor($parametroDtFinal2);
            $controle->set_linha(3);
            $controle->set_col(6);
            $form->add_item($controle);

            ###

            $controle = new Input('parametroDtInicial3', 'data', 'Data Inicial do Período 3:', 1);
            $controle->set_size(20);
            $controle->set_title('Informe a data inicial do período 3');
            $controle->set_valor($parametroDtInicial3);
            $controle->set_linha(3);
            $controle->set_col(6);
            $form->add_item($controle);

            $controle = new Input('parametroDtFinal3', 'data', 'Data Final do Período 3:', 1);
            $controle->set_size(20);
            $controle->set_title('Informe a data final do período 3');
            $controle->set_valor($parametroDtFinal3);
            $controle->set_linha(3);
            $controle->set_col(6);
            $form->add_item($controle);

            ###

            $controle = new Input('parametroDtInicial4', 'data', 'Data Inicial do Período 4:', 1);
            $controle->set_size(20);
            $controle->set_title('Informe a data inicial do período 4');
            $controle->set_valor($parametroDtInicial4);
            $controle->set_linha(3);
            $controle->set_col(6);
            $form->add_item($controle);

            $controle = new Input('parametroDtFinal4', 'data', 'Data Final do Período 4:', 1);
            $controle->set_size(20);
            $controle->set_title('Informe a data final do período 4');
            $controle->set_valor($parametroDtFinal4);
            $controle->set_linha(3);
            $controle->set_col(6);
            $form->add_item($controle);
            
            ###

            $controle = new Input('parametroPublicacaoData', 'data', 'Publicação Sem Efeito:', 1);
            $controle->set_size(20);
            $controle->set_title('Informe a data da publicação a ficar sem efeito');
            $controle->set_valor($parametroPublicacaoData);
            $controle->set_linha(4);
            $controle->set_col(6);
            $form->add_item($controle);

            $controle = new Input('parametroPublicacaoPag', 'texto', 'Página:', 1);
            $controle->set_size(6);
            $controle->set_title('Página da publicação');
            $controle->set_valor($parametroPublicacaoPag);
            $controle->set_linha(4);
            $controle->set_col(3);
            $form->add_item($controle);

            $controle = new Input('parametroPreenchido', 'combo', 'Preenchido por:', 1);
            $controle->set_size(100);
            $controle->set_title('quem Preencheu');
            $controle->set_valor($parametroPreenchido);
            $controle->set_array($relacao);
            $controle->set_linha(5);
            $controle->set_col(6);
            $form->add_item($controle);

            $controle = new Input('parametroAcordo', 'combo', 'De Acordo:', 1);
            $controle->set_size(100);
            $controle->set_title('de Acordo');
            $controle->set_valor($parametroAcordo);
            $controle->set_array($relacao);
            $controle->set_linha(5);
            $controle->set_col(6);
            $form->add_item($controle);

            # submit
            $controle = new Input('submit', 'submit');
            $controle->set_valor('Imprimir');
            $controle->set_linha(4);
            $form->add_item($controle);

            $form->show();
            break;

        #######

        case "aguarde":
            if (empty($parametroMeses)
                    OR empty($parametroDtInicial)
                    OR empty($parametroDtFinal)
                    OR empty($parametroPreenchido)
                    OR empty($parametroAcordo)) {

                alert("Todos os campos devem ser preenchidos !!");
                back(1);
            } else {
                br(4);
                aguarde();
                br();

                # Limita a tela
                $grid1 = new Grid("center");
                $grid1->abreColuna(5);
                p("Aguarde...", "center");
                $grid1->fechaColuna();
                $grid1->fechaGrid();

                loadPage('?fase=folha');
            }


            break;

        #######

        case "folha" :

            ######
            # Trata os parêmetros
            $extenso = numero_to_letra($parametroMeses);
            $data1 = date_to_php($parametroDtInicial);
            $data2 = date_to_php($parametroDtFinal);

            $data3 = date_to_php($parametroDtInicial2);
            $data4 = date_to_php($parametroDtFinal2);

            $data5 = date_to_php($parametroDtInicial3);
            $data6 = date_to_php($parametroDtFinal3);

            $data7 = date_to_php($parametroDtInicial4);
            $data8 = date_to_php($parametroDtFinal4);

            # Monta o texto
            $texto = "O servidor faz jus à concessão de <b>{$parametroMeses} ($extenso) meses</b> de Licença Prêmio relativos";

            if ($parametroMeses == 3) {
                $texto .= " ao período de <b>{$data1} - {$data2}</b>";
            }

            if ($parametroMeses == 6) {
                $texto .= " aos períodos de <b>{$data1} - {$data2} e {$data3} - {$data4}</b>";
            }

            if ($parametroMeses == 9) {
                $texto .= " aos períodos de <b>{$data1} - {$data2}, {$data3} - {$data4} e {$data5} - {$data6}</b>";
            }

            if ($parametroMeses == 12) {
                $texto .= " aos períodos de <b>{$data1} - {$data2}, {$data3} - {$data4}, {$data5} - {$data6} e {$data7} - {$data8}</b>";
            }

            # Monta a Declaração
            $dec = new Declaracao();
            $dec->set_declaracaoNome("CERTIDÃO DE " . mb_strtoupper($parametroTipo) . " PARA LICENÇA PRÊMIO");
            $dec->set_texto("ID funcional nº: <b>{$pessoal->get_idFuncional($idServidorPesquisado)}</b>");
            $dec->set_texto("Nome: <b>" . strtoupper($pessoal->get_nome($idServidorPesquisado)) . "</b>");
            $dec->set_texto("Processo de Contagem: <b>{$licencaPremio->get_numProcessoContagem($idServidorPesquisado)}</b>");
            $dec->set_texto("Cargo: <b>{$pessoal->get_cargoSimples($idServidorPesquisado)}</b>");
            $dec->set_texto("Nível: <b>{$pessoal->get_nivelSalarialCargo($idServidorPesquisado)}</b>");
            $dec->set_texto("Assunto: <b>Licença Prêmio</b>");
            if ($parametroTipo == "Concessão") {
                $dec->set_texto("( x ) Concessão&nbsp;&nbsp;&nbsp;(&nbsp;&nbsp;&nbsp;) Recontagem&nbsp;&nbsp;&nbsp;(&nbsp;&nbsp;&nbsp;) Indeferimento");
            } else {
                $dec->set_texto("(&nbsp;&nbsp;&nbsp;) Concessão&nbsp;&nbsp;&nbsp;( x ) Recontagem&nbsp;&nbsp;&nbsp;(&nbsp;&nbsp;&nbsp;) Indeferimento");
            }
            $dec->set_texto("<hr/>");
            $dec->set_texto("Fundamento: Art. 129 do decreto2479/79, combinado coma Lei 1054/86");
            $dec->set_texto("<hr/>");
            $dec->set_texto("Sr. Gerente de Recursos Humanos,");            
            
            if ($parametroTipo == "Concessão") {
                $dec->set_texto("{$texto}.");
            } else {
                $data9 = date_to_php($parametroPublicacaoData);
                $dec->set_texto("{$texto}, tornando sem efeito a publicação de {$data9} página {$parametroPublicacaoPag}.");
            }
            
            $dec->set_texto("");
            $dec->set_texto("Preenchido por: <b>{$parametroPreenchido}</b>");
            $dec->set_texto("De Acordo: <b>{$parametroAcordo}</b>");
            $dec->set_saltoAssinatura(2);

            $dec->set_exibeData(false);
            $dec->set_exibeAssinatura(false);
            $dec->show();

            # Grava o log da visualização do relatório
            $data = date("Y-m-d H:i:s");
            $atividades = 'Visualizou a certidão de concessão de licença prêmio';
            $tipoLog = 4;
            $intra->registraLog($idUsuario, $data, $atividades, null, null, $tipoLog, $idServidorPesquisado);
            break;
    }

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
}    