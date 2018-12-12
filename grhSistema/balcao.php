<?php
/**
 * Cadastro de Servidores
 *  
 * By Alat
 */

# Reservado para o servidor logado
$idUsuario = NULL;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso){  
    
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase','lista');
    $editar = get('editar',0);
    
    # Verifica se veio menu grh e registra o acesso no log
    $origem = get('origem',FALSE);
    if($origem){
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de servidores";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
    }
    
    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    # Pega o mes e o ano
    $parametroAno = post('parametroAno',get_session('parametroAno',date('Y')));
    $parametroMes = post('parametroMes',get_session('parametroMes',date('m')));
        
    # Joga os parâmetros par as sessions    
    set_session('parametroAno',$parametroAno);
    set_session('parametroMes',$parametroMes);
    
    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();
    
    # Cabeçalho da Página
    AreaServidor::cabecalho();
    
    $grid1 = new Grid();
    $grid1->abreColuna(12);
    
    ################################################################
    
    switch ($fase){	
        # Exibe o Menu Inicial
        case "lista" :    
            # Cria um menu
            $menu1 = new MenuBar();

            # Sair da Área do Servidor
            $linkVoltar = new Link("Voltar","grh.php");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title('Voltar');
            $menu1->add_link($linkVoltar,"left");
            
            if($editar){
                $linkEditar = new Link("Editar","?editar=0");
            }else{
                $linkEditar = new Link("Editar","?editar=1");
            }
            $linkEditar->set_class('button');
            $linkEditar->set_title('Voltar');
            $menu1->add_link($linkEditar,"right");

            $menu1->show();
            
            # Formulário de Pesquisa
            $form = new Form('?');
            
            # Cria um array com os anos possíveis
            $anoAtual = date('Y');
            $anosPossiveis = arrayPreenche($anoAtual-1,$anoAtual+2);

            $controle = new Input('parametroAno','combo','Ano:',1);
            $controle->set_size(30);
            $controle->set_title('Ano');
            $controle->set_array($anosPossiveis);
            $controle->set_valor($parametroAno);
            $controle->set_autofocus(TRUE);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);

            $controle = new Input('parametroMes','combo','Lotação:',1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($mes);
            $controle->set_valor($parametroMes);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);
            
            $form->show();
            
            # Monta o relatório da folha de Presença

            # Cabeçalho
            echo '<table class="tabelaPadrao">';
            
            echo '<caption>Controle de Atendimento no Balcão</caption>';

            echo '<col style="width:5%">';
            echo '<col style="width:20%">';
            echo '<col style="width:20%">';
            echo '<col style="width:20%">';
            echo '<col style="width:35%">';

            # Cabeçalho
            echo '<tr>';
                echo '<th>DIA</th>';
                echo '<th>Dia da Semana</th>';
                echo '<th>Manha</th>';
                echo '<th>Tarde</th>';
                echo '<th>Obs</th>';
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
                $wday1=$Tdate["wday"];

                # Array dom os nomes do dia da semana 
                $diaSemana = array("Domingo","Segunda-feira","Terça-feira","Quarta-feira","Quinta-feira","Sexta-feira","Sabado");
                
                # Verifica se nesta data existe um feriado
                $feriado = $pessoal->get_feriado($data);
                    
                # inicia a linha do dia    
                echo '<tr';
                
                if(!is_null($feriado)){
                    echo ' id="feriado"';
                }elseif(($wday1 == 0) OR ($wday1 == 6)){
                    echo ' id="feriado"';
                }
                echo '>';

                # Exibe o número do dia
                echo '<td align="center">'.$contador.'</td>';
                
                # Exibe o nome da semana
                if(($wday1 == 0) OR ($wday1 == 6)){
                    echo '<td id="" align="center">'.$diaSemana[$wday1].'</td>';
                }else{
                    echo '<td align="center">'.$diaSemana[$wday1].'</td>';
                }

                # Coluna do codigo
                if(!is_null($feriado)){
                    echo '<td align="center">Feriado</td>';
                    echo '<td align="center">Feriado</td>';
                    echo '<td align="center">'.$feriado.'</td>';
                }elseif(($wday1 == 0) OR ($wday1 == 6)){
                    echo '<td align="center"><b><span id="f14">----------</span></b></td>';
                    echo '<td align="center"><b><span id="f14">----------</span></b></td>';
                    echo '<td align="center"><b><span id="f14">----------</span></b></td>';
                }else{
                    if($editar == 1){
                        $servidoresManha = array("Ana Paula","Andre","Claudia");
                        $servodiresTarde = array("Alberto","Ana Terezinha","Christiane");
                        
                        # Formulário de Pesquisa
                        $form = new Form('?');
            
                        echo '<td>';
                        $controle = new Input('manha'.$contador,'combo',NULL,0);
                        $controle->set_size(30);
                        $controle->set_title('manha'.$contador);
                        $controle->set_array($servidoresManha);
                        #$controle->set_valor($parametroAno);
                        $form->add_item($controle);
                        echo '</td>';
                        
                        echo '<td>';
                        $controle = new Input('tarde'.$contador,'combo',NULL,0);
                        $controle->set_size(30);
                        $controle->set_title('tarde'.$contador);
                        $controle->set_array($servodiresTarde);
                        #$controle->set_valor($parametroAno);
                        $form->add_item($controle);
                        echo '</td>';
                        
                        $form->show();
                        
                        echo '<td>&nbsp</td>';
                        
                    }else{
                        echo '<td>&nbsp</td>';
                        echo '<td>&nbsp</td>';
                        echo '<td>&nbsp</td>';
                    }
                }
                
                echo '</tr>';
            }

            echo '</table>';
            # data de impressão
            p('Emitido em: '.date('d/m/Y - H:i:s')." (".$idUsuario.")",'pRelatorioDataImpressao');

            
            break;
    }
            
    $grid1->fechaColuna();
    $grid1->fechaGrid();
    
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}