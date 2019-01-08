<?php
/**
 * Sistema GRH
 * 
 * Capa da Pasta do Servidor
 *   
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = NULL;              # Servidor logado
$idServidorPesquisado = NULL;	# Servidor Editado na pesquisa do sistema do GRH

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

# Pega o mes e o ano
$parametroAno = post('parametroAno',get_session('parametroAno',date('Y')));
$parametroMes = post('parametroMes',get_session('parametroMes',date('m')));

if($acesso)
{    
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
	
    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();
    
    $grid1 = new Grid("center");
    $grid1->abreColuna(11);

    # Grava no log a atividade
    $atividade = 'Visualizou o relatório de controle de balcão';
    $Objetolog = new Intra();
    $data = date("Y-m-d H:i:s");
    $Objetolog->registraLog($idUsuario,$data,$atividade,NULL,NULL,4,$idServidorPesquisado);
    
    # Menu do Relatório
    $menuRelatorio = new menuRelatorio();
    $menuRelatorio->show();

    # Cabeçalho
    $cabecalho = new Relatorio();
    $cabecalho->exibeCabecalho();

    echo '<table class="tabelaRelatorio" id="tableFolhaPresenca">';
	
    echo '<caption>Controle de Atendimento no Balcão</caption>';

    echo '<col style="width:10%">';
    echo '<col style="width:20%">';
    echo '<col style="width:35%">';
    echo '<col style="width:35%">';

    # Cabeçalho
    echo '<tr>';
        echo '<th>DIA</th>';
        echo '<th>Dia da Semana</th>';
        echo '<th>Manha</th>';
        echo '<th>Tarde</th>';
    echo '</tr>';

    # Verifica quantos dias tem o mês específico
    $dias = date("j",mktime(0,0,0,$parametroMes+1,0,$parametroAno));

    $contador = 0;
    while ($contador < $dias){	
        $contador++;

        # Define a data no formato americano (ano/mes/dia)
        $data = date("d/m/Y", mktime(0, 0, 0, $parametroMes , $contador, $parametroAno));

        # Determina o dia da semana numericamente
        $tstamp=mktime(0,0,0,$parametroMes,$contador,$parametroAno);
        $Tdate = getdate($tstamp);
        $wday=$Tdate["wday"];

        # Array dom os nomes do dia da semana 
        $diaSemana = array("Domingo","Segunda-feira","Terça-feira","Quarta-feira","Quinta-feira","Sexta-feira","Sabado");

        # Verifica se nesta data existe um feriado
        $feriado = $pessoal->get_feriado($data);

        # inicia a linha do dia    
        echo '<tr';

        if(!is_null($feriado)){
            echo ' id="feriado"';
        }elseif(($wday == 0) OR ($wday == 6)){
            echo ' id="feriado"';
        }
        echo '>';

        # Exibe o número do dia
        echo '<td align="center">'.$contador.'</td>';

        # Exibe o nome da semana
        if(($parametroAno == date('Y')) AND ($parametroMes == date('m')) AND ($contador == date('d'))){
            echo '<td align="center"><b>Hoje</b></td>';
        }else{
            echo '<td align="center">'.$diaSemana[$wday].'</td>';
        }

        # Coluna do codigo
        if(!is_null($feriado)){
            echo '<td colspan="2" align="center">'.$feriado.'</td>';
        }elseif(($wday == 0) OR ($wday == 6)){
            echo '<td colspan="2" align="center"><b><span id="f14">----------</span></b></td>';
        }else{

            # Define a regra de funcionamento para cada dia da semana seguindo o valor de $wday
            # Sendo: 
            #   n -> não tem atendimento; 
            #   m -> atendimento no turno da manhã; 
            #   t -> atendimento no turno da tarde; 
            #   a -> ambos
            $regraFuncionamento = array('n','t','m','a','t','m','n');   

            # Turno da manhã  
            if(($regraFuncionamento[$wday] == "m") OR ($regraFuncionamento[$wday] == "a")){
                $ditoCujo = get_servidorBalcao($parametroAno,$parametroMes,$contador,"m");
                echo '<td';

                if($ditoCujo == '?'){
                    echo ' id="ausente"';
                }
                echo ' align="center"><span id="f14">'.$ditoCujo.'</span></td>';
            }else{
                echo '<td align="center">-----</td>';
            }

            # Turno da Tarde
            if(($regraFuncionamento[$wday] == "t") OR ($regraFuncionamento[$wday] == "a")){
                $ditoCujo = get_servidorBalcao($parametroAno,$parametroMes,$contador,"t");
                echo '<td';

                if($ditoCujo == '?'){
                    echo ' id="ausente"';
                }
                echo ' align="center"><span id="f14">'.$ditoCujo.'</span></td>';
            }else{
                echo '<td align="center">-----</td>';
            }            
        }
        echo '</tr>';
    }
    
    echo '</table>';
    echo "<div style='page-break-before:always;'>&nbsp</div>";
    
    $grid1->fechaColuna();
    $grid1->fechaGrid();
    
    $page->terminaPagina();
}