<?php

class FolhaFrequencia {

    /**
     * Emite uma folha de frequencia de um servidor
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    ###########################################################

    public function __construct() {
        
    }

    ###########################################################

    function exibeFolha($idServidor = null, $anoBase = null, $trimestre = null, $idUsuario = null, $cabecalho = true) {

        # Trata as variáveis
        if (empty($idServidor)) {
            return null;
        }

        if (empty($anoBase)) {
            $anoBase = date('Y');
        }

        if (empty($trimestre)) {
            $trimestre = 1;
        }

        /*
         *  Cria array dos meses
         */
        $mes = array(array("1", "Janeiro"),
            array("2", "Fevereiro"),
            array("3", "Março"),
            array("4", "Abril"),
            array("5", "Maio"),
            array("6", "Junho"),
            array("7", "Julho"),
            array("8", "Agosto"),
            array("9", "Setembro"),
            array("10", "Outubro"),
            array("11", "Novembro"),
            array("12", "Dezembro"));

        $nomeMes = array(null,
            "Janeiro",
            "Fevereiro",
            "Março",
            "Abril",
            "Maio",
            "Junho",
            "Julho",
            "Agosto",
            "Setembro",
            "Outubro",
            "Novembro",
            "Dezembro");

        # Conecta ao Banco de Dados    
        $pessoal = new Pessoal();

        ######
        # Corpo do relatorio        
        $select = 'SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,                 
                     tbservidor.idServidor,
                     tbservidor.dtAdmissao
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
               WHERE tbservidor.idServidor = ' . $idServidor;

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioProcessosArquivados');
        $relatorio->set_conteudo($result);
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
        $relatorio->set_linhaNomeColuna(false);
        $relatorio->set_menuRelatorio($cabecalho);
        
        $relatorio->set_logServidor($idServidor);
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
                    $afastClass = new VerificaAfastamentos($idServidor);
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
    }

}
