<?php

/**
 * Sistema GRH
 * 
 * Folha de Presença
 *   
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;
$idServidorPesquisado = null;

# Configuração
include ("../grhSistema/_config.php");

# Pega os parâmetros dos relatórios
$anoBase = (int) post('anoBase', date('Y'));
$mesBase = (int) post('mes', date('m'));

# Permissão de Acesso
$acesso = $acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

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
    $select = "SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,                 
                     tbservidor.idServidor,
                     tbservidor.dtAdmissao
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
               WHERE tbservidor.idServidor = {$idServidorPesquisado}";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio('relatorioProcessosArquivados');
    $relatorio->set_cabecalho_exibeDiretoriaGerencia(false);
    $relatorio->set_titulo('ANEXO IV - Controle de Frequência Diária');
    $relatorio->set_tituloLinha2("Mês {$nomeMes[$mesBase]} Ano " . $anoBase);
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
        array('nome' => 'mes',
            'label' => 'Mês:',
            'tipo' => 'combo',
            'array' => $mes,
            'size' => 10,
            'padrao' => $mesBase,
            'col' => 3,
            'onChange' => 'formPadrao.submit();',
            'linha' => 1)));

    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->set_logDetalhe("Visualizou a Folha de Presença Mensal de {$nomeMes[(int) $mesBase]} / {$anoBase}");
    $relatorio->set_dataImpressao(false);
    $relatorio->show();

    br();

    # Monta o relatório da folha de Presença
    # Cabeçalho
    echo '<table class="tabelaRelatorio" id="tableFolhaPresenca">';

    echo '<col style="width:20%">';
    echo '<col style="width:20%">';
    echo '<col style="width:20%">';
    echo '<col style="width:20%">';
    echo '<col style="width:20%">';

    # Cabeçalho
    echo '<tr>';
    echo '<th><b>DIA</b></th>';
    echo '<th><b>Entrada</br>08:00</b></th>';
    echo '<th><b>Saída para Almoço</br>12:00</b></th>';
    echo '<th><b>Retorno do Almoço</br>14:00</b></th>';
    echo '<th><b>Saída</br>18:00</b></th>';
    echo '</tr>';

    # Verifica quantos dias tem o mês específico
    $dias = date("j", mktime(0, 0, 0, $mesBase + 1, 0, $anoBase));

    $contador = 0;
    while ($contador < $dias) {
        $contador++;
        echo '<tr>';

        # Exibe o número do dia
        echo "<td align='center'>" . str_pad($contador, 2, "0", STR_PAD_LEFT) . " / " . str_pad($mesBase, 2, "0", STR_PAD_LEFT) . " / {$anoBase}</td>";

        if ($contador <= $dias) {
            # Cria variavel com a data no formato americano (ano/mes/dia)
            $data = date("d/m/Y", mktime(0, 0, 0, $mesBase, $contador, $anoBase));

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
                echo "<td align='center'>{$feriado}</td>";
                echo "<td align='center'>{$feriado}</td>";
                echo "<td align='center'>{$feriado}</td>";
                echo "<td align='center'>{$feriado}</td>";
            } elseif (!empty($afastamento)) {     // verifica se tem licença
                echo "<td align='center'>{$afastamento}</td>";
                echo "<td align='center'>{$afastamento}</td>";
                echo "<td align='center'>{$afastamento}</td>";
                echo "<td align='center'>{$afastamento}</td>";
            } else {

                $tstamp = mktime(0, 0, 0, $mesBase, $contador, $anoBase);
                $Tdate = getdate($tstamp);
                $wday1 = $Tdate["wday"];

                switch ($wday1) {
                    case 0:
                        echo "<td align='center'><b>DOMINGO</b></td>";
                        echo "<td align='center'><b>DOMINGO</b></td>";
                        echo "<td align='center'><b>DOMINGO</b></td>";
                        echo "<td align='center'><b>DOMINGO</b></td>";
                        break;
                    case 6:
                        echo "<td align='center'><b>SÁBADO</b></td>";
                        echo "<td align='center'><b>SÁBADO</b></td>";
                        echo "<td align='center'><b>SÁBADO</b></td>";
                        echo "<td align='center'><b>SÁBADO</b></td>";
                        break;
                    default:
                        echo "<td>&nbsp</td>";
                        echo "<td>&nbsp</td>";
                        echo "<td>&nbsp</td>";
                        echo "<td>&nbsp</td>";
                        break;
                }
            }
        } else {
            echo "<td>------------</td>";
        }

        # Coluna do codigo // Não tem mais
        #echo '<td>&nbsp</td>';

        echo '</tr>';
    }

    echo '</table>';
    # data de impressão // retirada a pedido de Chris
    #p('Emitido em: ' . date('d/m/Y - H:i:s') . " (" . $idUsuario . ")", 'pRelatorioDataImpressao');

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

    p("<br/>Obs: A frequência será registrada diariamente mediante assinatura do servidor participante"
            . " do programa nos horários determinados no artigo 14 da Resolução. Nos dias em que o horário"
            . " não for cumprido, a chefia imediata deverá registrar o atraso com exposição do horário"
            . " e rubrica no campo de assinatura.", "f14");

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
}