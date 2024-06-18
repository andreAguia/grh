<?php

class FolhaFrequencia {

    /**
     * Emite uma folha de frequencia de um servidor
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    # Obrigatórios
    private $idServidor = null;
    private $anoBase = null;
    private $trimestre = null;
    private $idUsuario = null;

    # Opcional
    private $menuRelatorio = true;
    private $cabecalho = true;

    ###########################################################

    public function __construct($anoBase = null, $trimestre = null, $idUsuario = null) {
        
        $this->anoBase = $anoBase;
        $this->trimestre = $trimestre;
        $this->idUsuario = $idUsuario;
    }

    ###########################################################

    public function set_menuRelatorio($menuRelatorio) {
        /**
         * Informa se o menu do relatório vai aparecer ou não
         *
         * @param $menuRelatorio bool true TRUE exibe o menu False não exibe
         *
         * @syntax $input->set_menuRelatorio($menuRelatorio);
         */
        $this->menuRelatorio = $menuRelatorio;
    }

    ###########################################################

    public function set_cabecalho($cabecalho) {
        /**
         * Informa se terá ou não o cabecalho
         *
         * @param $cabecalho bool true exibe ou não o cabecalho
         *
         * @syntax $input->set_cabecalho($cabecalho);
         */
        $this->cabecalho = $cabecalho;
    }

    ###########################################################

    function exibeFolha($idServidor = null) {
        
        $this->idServidor = $idServidor;

        # Trata as variáveis
        if (empty($this->idServidor)) {
            return null;
        }

        if (empty($this->anoBase)) {
            $anoBase = date('Y');
        }

        if (empty($this->trimestre)) {
            $trimestre = 1;
        }

        if (empty($this->idUsuario)) {
            return null;
        }

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
        $select = "SELECT tbservidor.idFuncional,
                          tbpessoa.nome,
                          tbservidor.idServidor,                 
                          tbservidor.idServidor,
                          tbservidor.dtAdmissao
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                    WHERE tbservidor.idServidor = {$this->idServidor}";

        $result = $pessoal->select($select);

        $relatorio = new Relatorio("relatorioProcessosArquivados");
        $relatorio->set_conteudo($result);
        $relatorio->set_titulo("Cartão de Frequência Trimestral");
        $relatorio->set_tituloLinha2("{$this->trimestre}° Trimestre / {$this->anoBase}");
        $relatorio->set_label(['IdFuncional', 'Nome', 'Cargo', 'Lotação', 'Admissão']);
        $relatorio->set_align(["center"]);
        $relatorio->set_funcao([null, null, null, null, "date_to_php"]);
        $relatorio->set_classe([null, null, "pessoal", "pessoal"]);
        $relatorio->set_metodo([null, null, "get_cargoComSaltoSemComissao", "get_Lotacao"]);

        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(false);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_linhaNomeColuna(false);
        $relatorio->set_menuRelatorio($this->menuRelatorio);

        $relatorio->set_logServidor($this->idServidor);
        $relatorio->set_logDetalhe("Visualizou a Folha de Presença de {$this->trimestre}° Trimestre / {$this->anoBase}");
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

        switch ($this->trimestre) {
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
                $dias = date("j", mktime(0, 0, 0, $mesInicial + 1 + $i, 0, $this->anoBase));

                if ($contador <= $dias) {
                    # Cria variavel com a data no formato americano (ano/mes/dia)
                    $data = date("d/m/Y", mktime(0, 0, 0, $mesInicial + $i, $contador, $this->anoBase));

                    # Verifica se o servidor está com afastamento
                    $afastClass = new VerificaAfastamentos($this->idServidor);
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

                        $tstamp = mktime(0, 0, 0, $mesInicial + $i, $contador, $this->anoBase);
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
        p('Emitido em: ' . date('d/m/Y - H:i:s') . " (" . $this->idUsuario . ")", 'pRelatorioDataImpressao');

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
        
        # Gera o salto de página
        echo "<p style='page-break-before:always'></p>";
    }
}
