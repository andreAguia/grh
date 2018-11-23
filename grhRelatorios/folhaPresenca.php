<?php
/**
 * Sistema GRH
 * 
 * Folha de Presença
 *   
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = NULL;              # Servidor logado
$idServidorPesquisado = NULL;	# Servidor Editado na pesquisa do sistema do GRH

# Configuração
include ("../grhSistema/_config.php");

# Pega os parâmetros dos relatórios
$anoBase = post('anoBase',date('Y'));
$trimestre = post('trimestre',1);

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario);

if($acesso){
    
    # Conecta ao Banco de Dados    
    $pessoal = new Pessoal();
    
    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();
    
    # Limita a página
    $grid = new Grid();
    $grid->abreColuna(12);
    
    ######

    # Corpo do relatorio        
    $select ='SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,                 
                     tbservidor.idServidor,
                     tbservidor.dtAdmissao
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
               WHERE tbservidor.idServidor = '.$idServidorPesquisado;

    $result = $pessoal->select($select);

    $relatorio = new Relatorio('relatorioProcessosArquivados');
    $relatorio->set_titulo('Cartão de Frequência Trimestral');
    $relatorio->set_tituloLinha2($trimestre.'° Trimestre / '.$anoBase);
    $relatorio->set_label(array('IdFuncional','Nome','Cargo','Lotação','Admissão'));
    #$relatorio->set_width(array(12,30,28,20,10));
    $relatorio->set_align(array("center"));
    $relatorio->set_funcao(array("dv",NULL,NULL,NULL,"date_to_php"));
    $relatorio->set_classe(array(NULL,NULL,"pessoal","pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,"get_Cargo","get_Lotacao"));
    $relatorio->set_subTotal(FALSE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_dataImpressao(FALSE);
    $relatorio->set_conteudo($result);
    $relatorio->set_linhaNomeColuna(FALSE);
    $relatorio->set_formCampos(array(
                               array ('nome' => 'anoBase',
                                      'label' => 'Ano:',
                                      'tipo' => 'texto',
                                      'size' => 4,
                                      'title' => 'Ano',
                                      'col' => 3,
                                      'padrao' => $anoBase,
                                      'onChange' => 'formPadrao.submit();',
                                      'linha' => 1),
                               array ('nome' => 'trimestre',
                                      'label' => 'Trimestre:',
                                      'tipo' => 'combo',
                                      'array' => array(array(1,'Primeiro'),array(2,'Segundo'),array(3,'Terceiro'),array(4,'Quarto')),
                                      'size' => 10,
                                      'padrao' => $trimestre,
                                      'title' => 'Mês',
                                      'col' => 3,
                                      'onChange' => 'formPadrao.submit();',
                                      'linha' => 1)));

    $relatorio->set_formFocus('anoBase');
    $relatorio->set_formLink('?');
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->set_logDetalhe("Visualizou a Folha de Presença");
    $relatorio->show();
    
    br();

    # Monta o relatório da folha de Presença

    # Cabeçalho
    echo '<table class="tabelaRelatorio" id="tableFolhaPresenca">';
    
    echo '<col style="width:5%">';
    echo '<col style="width:20%">';
    echo '<col style="width:5%">';
    echo '<col style="width:20%">';
    echo '<col style="width:5%">';
    echo '<col style="width:20%">';
    echo '<col style="width:5%">';
    
    switch ($trimestre){
        case 1:
            $mesInicial = 1;
            break;
        
        case 2:
            $mesInicial = 4;
            break;
        
        case 3:
            $mesInicial = 7;
            break;
        
        case 4:
            $mesInicial = 10;
            break;    
    }
    
    # Verifica quantos dias tem o mês específico
    $dias1 = date("j",mktime(0,0,0,$mesInicial+1,0,$anoBase));
    $dias2 = date("j",mktime(0,0,0,$mesInicial+2,0,$anoBase));
    $dias3 = date("j",mktime(0,0,0,$mesInicial+3,0,$anoBase));
    
    # Cabeçalho
    echo '<tr>';
        echo '<th>Dia</th>';
        echo '<th>'.$nomeMes[$mesInicial].'</th>';
        echo '<th>Codigo</th>';
        echo '<th>'.$nomeMes[$mesInicial+1].'</th>';
        echo '<th>Codigo</th>';
        echo '<th>'.$nomeMes[$mesInicial+2].'</th>';
        echo '<th>Codigo</th>';
    echo '</tr>';
    
    $contador = 0;
    while ($contador < 31){	
        $contador++;
        echo '<tr>';

        # Cria variavel com a data no formato americano (ano/mes/dia)
        #$data = date("d/m/Y", mktime(0, 0, 0, $mesBase , $contador, $anoBase));

        # Verifica se nesta data o servidor está com licença
        #$licenca = $pessoal->get_licenca($idServidorPesquisado,$data);
        $licenca = NULL;

        # Verifica se nesta data existe um feriado
        #$feriado = $pessoal->get_feriado($data); 
        $feriado = NULL;
        
        # Verifica se nesta data o servidor está em férias
        #$ferias = $pessoal->emFerias($idServidorPesquisado, $data);
        $ferias = NULL;

        # Exibe o número do dia
        echo '<td align="center">'.$contador.'</td>';
        
        ####################
        # Primeira coluna
        ####################
        
        if($contador <= $dias1){
            $tstamp=mktime(0,0,0,$mesInicial,$contador,$anoBase);
            $Tdate = getdate($tstamp);
            $wday1=$Tdate["wday"];

            switch ($wday1){
                case 0:
                    echo '<td align="center">Domingo</td>';
                    break;	
                case 6:
                    echo '<td align="center">Sábado</td>';
                    break;		
                default:
                    echo '<td>&nbsp</td>';
                    break;
            }
        }else{
            echo '<td>------------</td>';
        }

        # Coluna do codigo
        echo '<td>&nbsp</td>';
        
        ####################
        # Segunda coluna 
        ####################
        if($contador <= $dias2){
            $tstamp=mktime(0,0,0,$mesInicial+1,$contador,$anoBase);
            $Tdate = getdate($tstamp);
            $wday2=$Tdate["wday"];

            switch ($wday2){
                case 0:
                    echo '<td align="center">Domingo</td>';
                    break;	
                case 6:
                    echo '<td align="center">Sábado</td>';
                    break;		
                default:
                    echo '<td>&nbsp</td>';
                    break;
            }
        }else{
            echo '<td>------------</td>';
        }

        # Coluna do codigo
        echo '<td>&nbsp</td>';

        ####################
        # Terceira coluna 
        ####################
        if($contador <= $dias3){
            $tstamp=mktime(0,0,0,$mesInicial+2,$contador,$anoBase);
            $Tdate = getdate($tstamp);
            $wday3=$Tdate["wday"];

            switch ($wday3){
                case 0:
                    echo '<td align="center">Domingo</td>';
                    break;	
                case 6:
                    echo '<td align="center">Sábado</td>';
                    break;		
                default:
                    echo '<td>&nbsp</td>';
                    break;
            }
        }else{
            echo '<td>------------</td>';
        }

        # Coluna do codigo
        echo '<td>&nbsp</td>';
        
        echo '</tr>';
    }
    
    echo '</table>';
    br();
    echo '<table class="tabelaRelatorio" id="tableFolhaPresenca2">';
    echo '<tr>';
    echo '<td>______________________________________________</td>';
    echo '<td>______________________________________________</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td>Assinatura da Chefia Imediata</td>';
    echo '<td>Assinatura do Servidor</td>';
    echo '</tr>';
    echo '<tr>';
    echo '</table>';

    # data de impressão
    p('Emitido em: '.date('d/m/Y - H:i:s'),'pRelatorioDataImpressao');
    
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    $page->terminaPagina();
}