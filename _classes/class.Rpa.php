<?php

class Rpa
{

    /**
     * Classe que abriga as várias rotina de RPA
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    ###########################################################

    public function get_dados($idRecibo = null)
    {
        /**
         * Retorna todos os dados 
         * 
         * @syntax $this->get_dados($idRpa);
         */
        if (empty($idRecibo)) {
            return null;
        } else {
            # Pega os dados
            $select = "SELECT *,
                              ADDDATE(dtInicial,dias-1) as dtFinal
                       FROM tbrpa_recibo
                      WHERE idRecibo = $idRecibo";

            $pessoal = new Pessoal();
            return $pessoal->select($select, false);
        }
    }

     ###########################################################

    public function getValor($idRecibo = null)
    {
        /**
         * Retorna o valor do serviço
         * 
         * @syntax $this->get_valor($idRpa);
         */
        if (empty($idRecibo)) {
            return null;
        } else {
            # Pega os dados
            $select = "SELECT valor
                       FROM tbrpa_recibo
                      WHERE idRecibo = $idRecibo";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select, false);
            return $row["valor"];
        }
    }

    ##############################################################

    public function exibeValorTotal($idRecibo)
    {
        /*
         * Exibe o Valor
         */

        # Pega os dados deste rpa
        $dados = $this->get_dados($idRecibo);

        # Pega o valor do INSS
        $inss = new RpaInss();
        $valorInss = $inss->getValor($idRecibo);

        # Pega o valor do IR
        $ir = new RpaIr();
        $valorIr = $ir->getValor($idRecibo);

        p(formataMoeda2($dados["valor"] - $valorInss[0] - $valorIr[0]), "pvalor");
    }

    ###########################################################

    public function exibeBotaoRpa($idRecibo)
    {

        # Conecta com o banco de dados
        $pessoal = new Pessoal();

        # Link do CI
        $botao = new BotaoGrafico();
        $botao->set_url("../grhRelatorios/rpa.php?id={$idRecibo}");
        $botao->set_target('_blank');
        $botao->set_imagem(PASTA_FIGURAS . 'printer.png', 20, 20);
        $botao->show();
    }

    ##############################################################

    public function exibePeriodo($idRecibo)
    {
        /*
         * Exibe a data Inicial os dias e a data final
         */

        if (empty($idRecibo)) {
            return null;
        } else {
            # Pega os dados
            $select = "SELECT dtInicial,
                              dias,
                              ADDDATE(dtInicial,dias-1) as dtFinal
                       FROM tbrpa_recibo
                      WHERE idRecibo = $idRecibo";

            $pessoal = new Pessoal();
            $dados = $pessoal->select($select, false);

            return date_to_php($dados["dtInicial"]) . "<br/>(" . $dados["dias"] . " dias)<br/>" . date_to_php($dados["dtFinal"]);
        }
    }

    ##############################################################

    public function exibeValores($idRecibo)
    {
        /*
         * Exibe a data Inicial os dias e a data final
         */

        if (empty($idRecibo)) {
            return null;
        } else {
            # Pega os dados deste rpa
            $dados = $this->get_dados($idRecibo);

            # Pega o valor do INSS
            $inss = new RpaInss();
            $valorInss = $inss->getValor($idRecibo);

            # Pega o valor do IR
            $ir = new RpaIr();
            $valorIr = $ir->getValor($idRecibo);

            $valores = [
                ["Valor:", formataMoeda2($dados["valor"])],
                ["INSS:",$valorInss[1]. "% (" . formataMoeda2($valorInss[0]) . ")"],
                ["IRRS:",$valorIr[1]. "% (" . formataMoeda2($valorIr[0]) . ")"],
                ["Total:", formataMoeda2($dados["valor"] - $valorInss[0] - $valorIr[0])]
            ];
            
            $tabela = new Tabela(null, "tabelaRpa");
            $tabela->set_conteudo($valores);
            $tabela->set_label([null, null]);
            $tabela->set_align(["left", "right"]);
            $tabela->set_totalRegistro(false);
            
            $formatacaoCondicional = array(
                array(
                    'coluna' => 0,
                    'valor' => "Total:",
                    'operador' => '=',
                    'id' => 'trRpaBlue'),
                array(
                    'coluna' => 0,
                    'valor' => "Valor:",
                    'operador' => '=',
                    'id' => 'trRpaBlue'),
                array(
                    'coluna' => 0,
                    'valor' => "INSS:",
                    'operador' => '=',
                    'id' => 'trRpaRed'),
                array(
                    'coluna' => 0,
                    'valor' => "IRRS:",
                    'operador' => '=',
                    'id' => 'trRpaRed'),
            );
            $tabela->set_formatacaoCondicional($formatacaoCondicional);
            $tabela->show();
        }
    }

    ##############################################################

    public function exibeProcessoRubrica($idRecibo)
    {
        /*
         * Exibe o processo e a rubrica do RPA
         */

        if (empty($idRecibo)) {
            return null;
        } else {
            # Pega os dados
            $select = "SELECT processo,
                              rubrica
                       FROM tbrpa_recibo
                      WHERE idRecibo = $idRecibo";

            $pessoal = new Pessoal();
            $dados = $pessoal->select($select, false);

            return "{$dados['processo']}<hr id='alerta'/>{$dados['rubrica']}";
        }
    }

    ##############################################################

}
