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
$mesBase = post('mesBase', date('m'));

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
    $relatorio->set_titulo('Folha de Presença');
    $relatorio->set_tituloLinha2(get_nomeMes($mesBase) . '/' . $anoBase);
    $relatorio->set_label(array('IdFuncional', 'Nome', 'Cargo', 'Lotação', 'Admissão'));
    #$relatorio->set_width(array(12,30,28,20,10));
    $relatorio->set_align(array("center"));
    $relatorio->set_funcao(array("dv", null, null, null, "date_to_php"));
    $relatorio->set_classe(array(null, null, "pessoal", "pessoal"));
    $relatorio->set_metodo(array(null, null, "get_Cargo", "get_Lotacao"));
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
        array('nome' => 'mesBase',
            'label' => 'Mês:',
            'tipo' => 'combo',
            'array' => $mes,
            'size' => 10,
            'padrao' => $mesBase,
            'title' => 'Mês',
            'col' => 3,
            'onChange' => 'formPadrao.submit();',
            'linha' => 1)));

    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->set_logDetalhe("Visualizou a Folha de Presença");
    $relatorio->show();

    br();

    # Monta o relatório da folha de Presença
    # Cabeçalho
    echo '<table class="tabelaRelatorio" id="tableFolhaPresenca">';

    echo '<col style="width:5%">';
    echo '<col style="width:15%">';
    echo '<col style="width:15%">';
    echo '<col style="width:15%">';
    echo '<col style="width:50%">';

    echo '<tr>';
    echo '<th>Dia</th>';
    echo '<th>Entrada</th>';
    echo '<th>Saída</th>';
    echo '<th>Rubrica</th>';
    echo '<th>Observações</th>';
    echo '</tr>';

    # Verifica quantos dias tem o mês específico
    $dias = date("j", mktime(0, 0, 0, $mesBase + 1, 0, $anoBase));

    $contador = 0;
    while ($contador < $dias) {
        $contador++;
        echo '<tr>';

        # Cria variavel com a data no formato americano (ano/mes/dia)
        $data = date("d/m/Y", mktime(0, 0, 0, $mesBase, $contador, $anoBase));

        # Verifica se nesta data o servidor está com licença
        $licenca = $pessoal->get_licenca($idServidorPesquisado, $data);

        # Verifica se nesta data existe um feriado
        #$feriado = $pessoal->get_feriado($data); 
        $feriado = null;

        # Verifica se nesta data o servidor está em férias
        $ferias = $pessoal->emFerias($idServidorPesquisado, $data);

        # Verifica que dia da semana é
        $tstamp = mktime(0, 0, 0, $mesBase, $contador, $anoBase);
        $Tdate = getdate($tstamp);
        $wday = $Tdate["wday"];

        # Exibe o número do dia
        echo '<td align="center">' . $contador . '</td>';

        # Verifica se é o dia do feriado
        if (!(is_null($feriado))) {
            echo '<td align="center">FERIADO</td>';
            echo '<td align="center">-------</td>';
            echo '<td align="center">-------</td>';
            echo '<td align="center">' . $feriado . '</td>';
        } elseif (!is_null($licenca)) {
            echo '<td align="center">LICENÇA</td>';
            echo '<td align="center">-------</td>';
            echo '<td align="center">-------</td>';
            echo '<td align="center">' . $licenca . '</td>';
        } elseif ($ferias) {
            echo '<td align="center">FÉRIAS</td>';
            echo '<td align="center">-------</td>';
            echo '<td align="center">-------</td>';
            echo '<td align="center">FÉRIAS</td>';
        } else {
            switch ($wday) {
                case 0:
                    echo '<td align="center">Domingo</td>';
                    echo '<td align="center">-------</td>';
                    echo '<td align="center">-------</td>';
                    echo '<td align="center">-------</td>';
                    break;
                case 6:
                    echo '<td align="center">Sábado</td>';
                    echo '<td align="center">-------</td>';
                    echo '<td align="center">-------</td>';
                    echo '<td align="center">-------</td>';
                    break;
                default:
                    echo '<td>&nbsp</td>';
                    echo '<td>&nbsp</td>';
                    echo '<td>&nbsp</td>';
                    echo '<td>&nbsp</td>';
                    break;
            }
        }
        echo '</tr>';
    }
    echo '</table>';

    # Nota de rodapé
    p('Considerando o Intervalo de 1 hora para o Almoço', 'pFolhaPresencaRodape');
    br(2);

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
    p('Emitido em: ' . date('d/m/Y - H:i:s'), 'pRelatorioDataImpressao');

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
}