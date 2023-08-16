<?php

class RpaIr {

    /**
     * Abriga as várias rotina referentes a Rpa
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    ##############################################################

    public function getUltimaDataDigitada() {
        /*
         * Informa a data da ultima IR digitada para 
         * informar o formulário de inclusão
         */

        # acessa o banco
        $pessoal = new Pessoal();
        $row = $pessoal->select("SELECT dtInicial FROM tbrpa_ir ORDER BY idIr desc LIMIT 1", false);

        if (empty($row[0])) {
            return null;
        } else {
            return $row[0];
        }
    }

    ##############################################################

    private function getDtInicialTabela($dtPgto = null) {
        /*
         * Informa a data inicial da tabela a partir da data do pgto
         * Se a data estiver em branco será informada a data vigante
         */

        # Conecta
        $pessoal = new Pessoal();

        # Verifica se a data foi informada ou se é a data Vigente
        if (empty($dtPgto)) {

            # Pega a última data cadastrada na tabela
            $row = $pessoal->select("SELECT dtInicial FROM tbrpa_ir ORDER BY dtInicial desc LIMIT 1", false);

            if (empty($row[0])) {
                return null;
            } else {
                return $row[0];
            }
        } else {

            # coloca o banco em um array
            $row1 = $pessoal->select("SELECT DISTINCT dtInicial FROM tbrpa_ir ORDER BY dtInicial", true, true);
            $dataEscolhida = null;

            # Percorre o array para analisar as datas
            foreach ($row1 as $dados) {
                # Verifica se a data da tabela é maior que 
                if (strtotime($dtPgto) >= strtotime($dados["dtInicial"])) {
                    $dataEscolhida = $dados["dtInicial"];
                }
            }

            return $dataEscolhida;
        }
    }

    ##############################################################

    private function getValorMaximoTabela($dtTabela) {
        /*
         * Informa o valor máximo da tabela de IR da data informada
         */

        # Conecta
        $pessoal = new Pessoal();

        # Pega a última data cadastrada na tabela        
        #$row = $pessoal->select("SELECT valorInicial FROM tbrpa_ir WHERE dtInicial = '{$dtTabela}' AND valorFinal = 0 ORDER BY valorInicial desc LIMIT 1", false);
        $row = $pessoal->select("SELECT valorFinal FROM tbrpa_ir WHERE dtInicial = '{$dtTabela}' ORDER BY valorFinal desc LIMIT 1", false);
        return $row[0];
    }

    ##############################################################

    private function getAliquotaMaximaTabela($dtTabela) {
        /*
         * Informa a aliquota máxima da tabela de Inss da data informada
         */

        # Conecta
        $pessoal = new Pessoal();

        # Pega a última data cadastrada na tabela
        $row = $pessoal->select("SELECT aliquota FROM tbrpa_ir WHERE dtInicial = '{$dtTabela}' ORDER BY valorInicial desc LIMIT 1", false);
        return $row[0];
    }

    ##############################################################

    private function getDeducaoMaximaTabela($dtTabela) {
        /*
         * Informa a dedução máxima da tabela de Inss da data informada
         */

        # Conecta
        $pessoal = new Pessoal();

        # Pega a última data cadastrada na tabela
        $row = $pessoal->select("SELECT deducao FROM tbrpa_ir WHERE dtInicial = '{$dtTabela}' ORDER BY valorInicial desc LIMIT 1", false);
        return $row[0];
    }

    ##############################################################

    public function exibeValor($idRecibo) {
        /*
         * Exibe o Valor
         */

        # Pega o valor
        $valor = $this->getValor($idRecibo);

        p(formataMoeda2($valor[0]), "pvalor");
        p("({$valor[1]}%)", "paliquota");
    }

    ##############################################################

    public function getValor($idRecibo) {
        /*
         * Faz o cálculo do Inss de um Rpa
         */

        # Pega os dados deste rpa
        $rpa = new Rpa();
        $dados = $rpa->get_dados($idRecibo);

        # Pega data Inicial da tabela IR dessa rpa
        $dtTabela = $this->getDtInicialTabela($dados["dtInicial"]);

        # Pega o Valor máximo dessa tabela
        $ultimoValor = $this->getValorMaximoTabela($dtTabela);

        # Inicia os valores
        $deducao = 0;
        $aliquota = 0;

        # Verifica se o valor ultrapassa o valor máximo
        if ($dados["valor"] < $ultimoValor) {

            # Pega a aliquota referente a este valor e esta data
            $aliquota = $this->getAliquota($dados["valor"], $dados["dtInicial"]);
            $deducao = $this->getDeducao($dados["valor"], $dados["dtInicial"]);
            $aliquotaCalculo = str_replace(",", ".", $aliquota);

            return [$dados["valor"] * ($aliquotaCalculo / 100), $aliquota, $deducao];
        } else {
            # Pega a aliquota máxima
            $aliquota = $this->getAliquotaMaximaTabela($dtTabela);
            $deducao = $this->getDeducaoMaximaTabela($dtTabela);
            $aliquotaCalculo = str_replace(",", ".", $aliquota);

            return [$dados["valor"] * ($aliquotaCalculo / 100), $aliquota, $deducao];
        }
    }

    ##############################################################

    public function getAliquota($valor, $dtPgto) {
        /*
         * informa a aliquota
         */

        # Pega a data da tabela
        $dtInicialtabela = $this->getDtInicialTabela($dtPgto);

        # Pega a aliquota de acordo com o valor
        $pessoal = new Pessoal();
        $row = $pessoal->select("SELECT aliquota
                                    FROM tbrpa_ir
                                   WHERE {$valor} BETWEEN ValorInicial AND ValorFinal
                                     AND dtInicial = '{$dtInicialtabela}'", false, true);

        return str_replace(",", ".", $row["aliquota"]);
    }

    ##############################################################

    public function getDeducao($valor, $dtPgto) {
        /*
         * informa a dedução
         */

        # Pega a data da tabela
        $dtInicialtabela = $this->getDtInicialTabela($dtPgto);

        # Pega a aliquota de acordo com o valor
        $pessoal = new Pessoal();
        $row = $pessoal->select("SELECT deducao
                                    FROM tbrpa_ir
                                   WHERE {$valor} BETWEEN ValorInicial AND ValorFinal
                                     AND dtInicial = '{$dtInicialtabela}'", false, true);

        return $row["deducao"];
    }

    ##############################################################

    public function exibeTabela() {
        /*
         * Exibe a tabela vigente do IR
         */

        # Pega a data da tabela vigente
        $dtInicial = $this->getDtInicialTabela();

        if (empty($dtInicial)) {
            titulotable("Tabela IR");
            $painel = new Callout();
            $painel->abre();
            br(2);
            p("Não existe tabela cadastrada!", "center");
            br(2);
            $painel->fecha();
        } else {

            # pega os dados da tabela desta data
            $pessoal = new Pessoal();
            $row = $pessoal->select("SELECT valorInicial,
                                        valorFinal,
                                        CONCAT(aliquota,' %'),
                                        deducao
                                   FROM tbrpa_ir
                                  WHERE dtInicial = '{$dtInicial}'
                               ORDER BY dtInicial");

            $tabela = new Tabela();
            $tabela->set_titulo("Tabela Vigente do IR");
            $tabela->set_conteudo($row);
            $tabela->set_label(["Valor Inicial", "Valor Final", "Aliquota", "Dedução"]);
            $tabela->set_funcao(["formataMoeda2", "formataMoeda2", null, "formataMoeda2"]);
            $tabela->set_width([25, 25, 25, 25]);
            $tabela->set_align(["center"]);
            $tabela->set_mensagemPreTabela("Desde: " . date_to_php($dtInicial));
            $tabela->show();
        }
    }

    ##############################################################
}
