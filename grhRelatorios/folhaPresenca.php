<?php

/**
 * Sistema GRH
 * 
 * Folha de Presença
 *   
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null; # Servidor Editado na pesquisa do sistema do GRH
# Configuração
include ("../grhSistema/_config.php");

# Pega os parâmetros dos relatórios
$anoBase = post('anoBase', date('Y'));
$trimestre = post('trimestre', 1);

# Permissão de Acesso
$acesso = $acesso = Verifica::acesso($idUsuario,[1, 2, 12]);

if ($acesso) {

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
    $select = 'SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,                 
                     tbservidor.idServidor,
                     tbservidor.dtAdmissao
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
               WHERE tbservidor.idServidor = ' . $idServidorPesquisado;

    $result = $pessoal->select($select);

    $relatorio = new Relatorio('relatorioProcessosArquivados');
    $relatorio->set_titulo('Cartão de Frequência Trimestral');
    $relatorio->set_tituloLinha2($trimestre . '° Trimestre / ' . $anoBase);
    $relatorio->set_label(['IdFuncional', 'Nome', 'Cargo', 'Lotação', 'Admissão']);
    $relatorio->set_align(["center"]);
    
    $relatorio->set_funcao([null, null, null, null, "date_to_php"]);
    $relatorio->set_classe([null, null, "pessoal", "pessoal"]);
    $relatorio->set_metodo([null, null, "get_cargoComSaltoSemComissao", "get_Lotacao"]);
    
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_conteudo($result);
    $relatorio->set_linhaNomeColuna(false);
    $relatorio->set_formCampos(array(
        array('nome' => 'anoBase',
            'label' => 'Ano:',
            'tipo' => 'texto',
            'size' => 4,
            'title' => 'Ano',
            'col' => 3,
            'padrao' => $anoBase,
            'onChange' => 'formPadrao.submit();',
            'linha' => 1),
        array('nome' => 'trimestre',
            'label' => 'Trimestre:',
            'tipo' => 'combo',
            'array' => array(array(1, 'Primeiro'), array(2, 'Segundo'), array(3, 'Terceiro'), array(4, 'Quarto')),
            'size' => 10,
            'padrao' => $trimestre,
            'col' => 3,
            'onChange' => 'formPadrao.submit();',
            'linha' => 1)));

    $relatorio->set_formFocus('anoBase');
    $relatorio->set_formLink('?');
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->set_logDetalhe("Visualizou a Folha de Presença de " . $trimestre . "° Trimestre / $anoBase");
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

    switch ($trimestre) {
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

    # Cabeçalho
    echo '<tr>';
    echo '<th><b>DIA</b></th>';
    echo '<th><b>' . mb_strtoupper($nomeMes[$mesInicial]) . '</b></th>';
    echo '<th><b>COD</b></th>';
    echo '<th><b>' . mb_strtoupper($nomeMes[$mesInicial + 1]) . '</b></th>';
    echo '<th><b>COD</b></th>';
    echo '<th><b>' . mb_strtoupper($nomeMes[$mesInicial + 2]) . '</b></th>';
    echo '<th><b>COD</b></th>';
    echo '</tr>';

    $contador = 0;
    while ($contador < 31) {
        $contador++;
        echo '<tr>';

        # Exibe o número do dia
        echo '<td align="center">' . $contador . '</td>';

        # Repete 3 vezes. Uma para cada coluna
        for ($i = 0; $i <= 2; $i++) {

            # Verifica quantos dias tem o mês específico
            $dias = date("j", mktime(0, 0, 0, $mesInicial + 1 + $i, 0, $anoBase));

            if ($contador <= $dias) {
                # Cria variavel com a data no formato americano (ano/mes/dia)
                $data = date("d/m/Y", mktime(0, 0, 0, $mesInicial + $i, $contador, $anoBase));

                # Verifica se o servidor está com afastamento
                $afastClass = new VerificaAfastamentos($idServidorPesquisado);
                $afastClass->setPeriodo($data);
                $afastClass->verifica();
                $afastamento = $afastClass->getAfastamento();
                $detalhe = $afastClass->getDetalhe();

                # Verifica se nesta data existe um feriado
                $feriado = $pessoal->get_feriado($data);

                # informa as ocorrências                
                if (!empty($feriado)) {     // verifica se tem feriado
                    echo '<td align="center">' . $feriado . '</td>';
                } elseif (!empty($afastamento)) {     // verifica se tem licença
                    echo '<td align="center">' . $afastamento . '</td>';
                } else {

                    $tstamp = mktime(0, 0, 0, $mesInicial + $i, $contador, $anoBase);
                    $Tdate = getdate($tstamp);
                    $wday1 = $Tdate["wday"];

                    switch ($wday1) {
                        case 0:
                            echo '<td align="center"><b>DOMINGO</b></td>';
                            break;
                        case 6:
                            echo '<td align="center"><b>SÁBADO</b></td>';
                            break;
                        default:
                            echo '<td>&nbsp</td>';
                            break;
                    }
                }
            } else {
                echo '<td>------------</td>';
            }

            # Coluna do codigo
            echo '<td>&nbsp</td>';
        } // for

        echo '</tr>';
    }

    echo '</table>';
    # data de impressão
    p('Emitido em: ' . date('d/m/Y - H:i:s') . " (" . $idUsuario . ")", 'pRelatorioDataImpressao');

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

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
}