<?php

class AposentadoriaTransicaoPontos1 {

    /**
     * Aposentadoria Regras de Transição Pontos 1
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    # Id Servidor
    private $idServidor = null;

    # Descrição
    private $descricao = "Regra dos Pontos<br/>Por Idade e Tempo de Contribuição<br/>Integralidade e Paridade - art. 3º da EC nº 90/2021";

    # Regras
    private $idadeHomem = 65;
    private $idadeMulher = 62;
    private $dtIngresso = "31/12/2003";
    private $contribuicaoHomem = 35;
    private $contribuicaoMulher = 30;
    private $servicoPublico = 20;
    private $cargoEfetivo = 5;
    private $pontosHomem = 96;
    private $pontosMulher = 86;
    private $regraIdade = null;
    private $regraContribuicao = null;

    # Remuneração
    private $calculoInicial = "Última remuneração";
    private $teto = "Remuneração do servidor no cargo efetivo";
    private $reajuste = "Na mesma data e índice dos servidores ativos";
    private $paridade = "COM PARIDADE";

    # Descrições
    private $dtIngressoDescricao = "Data de ingresso no serviço público sem interrupção<br/>(Somente Tempo Estatutário).";
    private $tempoContribuiçãoDescricao = "Tempo Total averbado<br/>(público e privado).";
    private $pontuacaoInicialDescricao = "Pontuação Inicial.";
    private $idadeDescricao = "Idade do servidor.";
    private $tempoPublicoDescicao = "Tempo de todos os periodo públicos ininterruptos.";
    private $tempoCargoDescicao = "Tempo no mesmo órgão e mesmo cargo.";
    private $dtRequesitosCumpridosDescicao = "Data limite para o cumprimento dos requesito.";

    # Dados do Servidor
    public $servidorDataNascimento = null;
    public $servidorIdade = null;
    public $servidorSexo = null;
    public $serviçoTempoTotal = null;
    public $servidorDataIngresso = null;
    public $servidorPontos = null;

    # Tempo do Servidor
    public $servidorTempoAverbadoPublico = null;
    public $servidorTempoAverbadoPrivado = null;
    public $servidorTempoUenf = null;
    public $servidorTempoTotal = null;
    public $servidorTempoPublicoIninterrupto = null;

    # Analises
    public $analisaDtIngresso = null;
    public $analiseIdade = null;
    public $analiseContribuicao = null;
    public $analisePublico = null;
    public $analiseCargoEfetivo = null;
    public $analiseDtRequesitosCumpridos = null;
    public $analisePontos = null;

    # Variaveis de Retorno    
    public $dataCriterioIngresso = null;
    public $dataCriterioIdade = null;
    public $dataCriterioPontos = null;
    public $dataCriterioTempoContribuicao = null;
    public $dataCriterioTempoServicoPublico = null;
    public $dataCriterioTempoCargo = null;
    public $dataDireitoAposentadoria = null;

    # Tabela de Pontos
    public $tabelaM = [
        [2023, 97],
        [2024, 97],
        [2025, 98],
        [2026, 98],
        [2027, 99],
        [2028, 99],
        [2029, 100],
        [2030, 100],
        [2031, 101],
        [2032, 101],
        [2033, 102],
        [2034, 102],
        [2035, 103],
        [2036, 103],
        [2037, 104],
        [2038, 104],
        [2039, 105],
        [2040, 105],
    ];
    public $tabelaF = [
        [2023, 87],
        [2024, 87],
        [2025, 88],
        [2026, 88],
        [2027, 89],
        [2028, 89],
        [2029, 90],
        [2030, 90],
        [2031, 91],
        [2032, 91],
        [2033, 92],
        [2034, 92],
        [2035, 93],
        [2036, 93],
        [2037, 94],
        [2038, 94],
        [2039, 95],
        [2040, 95],
        [2041, 96],
        [2042, 96],
        [2043, 97],
        [2044, 97],
        [2045, 98],
        [2046, 98],
        [2047, 99],
        [2048, 99],
    ];

    ###########################################################

    public function __construct($idServidor) {

        if (empty($idServidor)) {
            alert("O idServidor não foi Informado");
        } else {
            $this->idServidor = $idServidor;
        }

        # Pega os dados do servidor
        $pessoal = new Pessoal();
        $this->servidorDataNascimento = $pessoal->get_dataNascimento($this->idServidor);

        $this->servidorIdade = $pessoal->get_idade($this->idServidor);
        $this->servidorSexo = $pessoal->get_sexo($this->idServidor);

        $averbacao = new Averbacao();
        $this->servidorTempoAverbadoPublico = $averbacao->get_tempoAverbadoPublico($this->idServidor);
        $this->servidorTempoAverbadoPrivado = $averbacao->get_tempoAverbadoPrivado($this->idServidor);

        $aposentadoria = new Aposentadoria();
        $this->servidorTempoUenf = $aposentadoria->get_tempoServicoUenf($this->idServidor);
        $this->servidorDataIngresso = $aposentadoria->get_dtIngresso($this->idServidor);

        # Altera a data de ingresso para o servidor que tem tempo celetista Uenf 
        if ($aposentadoria->get_tempoServicoUenfCeletista($idServidor) > 0) {
            # Retorna a data da transformação em estatutários
            # Daqueles que entraram com celetistas na Uenf
            $this->servidorDataIngresso = "09/09/2003";
        }

        $this->servidorTempoTotal = $this->servidorTempoAverbadoPublico + $this->servidorTempoAverbadoPrivado + $this->servidorTempoUenf;
        $this->servidorTempoPublicoIninterrupto = $aposentadoria->get_tempoPublicoIninterrupto($this->idServidor);
        $this->servidorPontos = intval($this->servidorIdade + ($this->servidorTempoTotal / 365));

        if ($this->servidorSexo == "Masculino") {
            $this->regraIdade = $this->idadeHomem;
            $this->regraContribuicao = $this->contribuicaoHomem;
        } else {
            $this->regraIdade = $this->idadeMulher;
            $this->regraContribuicao = $this->contribuicaoMulher;
        }

        $hoje = date("d/m/Y");

        /*
         * Análise
         */

        # Data de Ingresso        
        if (dataMaior($this->dtIngresso, $this->servidorDataIngresso) == $this->dtIngresso) {
            $this->analisaDtIngresso = "OK";
        } else {
            $this->analisaDtIngresso = "NÃO TEM DIREITO";
        }

        # Idade
        $this->dataCriterioIdade = addAnos($this->servidorDataNascimento, $this->regraIdade);
        if ($this->servidorIdade >= $this->regraIdade) {
            $this->analiseIdade = "OK";
        } else {
            # Calcula a data
            $this->analiseIdade = "Somente em {$this->dataCriterioIdade}.";
        }

        # Tempo de Contribuição
        $resta1 = ($this->regraContribuicao * 365) - $this->servidorTempoTotal;
        $this->dataCriterioTempoContribuicao = addDias($hoje, $resta1);
        if ($this->servidorTempoTotal >= ($this->regraContribuicao * 365)) {
            $this->analiseContribuicao = "OK";
        } else {
            $this->analiseContribuicao = "Ainda faltam {$resta1} dias<br/>Somente em {$this->dataCriterioTempoContribuicao}.";
        }

        # Serviço Público Initerrupto
        $resta2 = ($this->servicoPublico * 365) - $this->servidorTempoPublicoIninterrupto;
        $this->dataCriterioTempoServicoPublico = addDias($hoje, $resta2);
        if ($this->servidorTempoPublicoIninterrupto >= ($this->servicoPublico * 365)) {
            $this->analisePublico = "OK";
        } else {
            $this->analisePublico = "Ainda faltam {$resta2} dias<br/>Somente em {$this->dataCriterioTempoServicoPublico}.";
        }

        # Cargo Efetivo
        $resta3 = ($this->cargoEfetivo * 365) - $this->servidorTempoUenf;
        $this->dataCriterioTempoCargo = addDias($hoje, $resta3);
        if ($this->servidorTempoUenf >= ($this->cargoEfetivo * 365)) {
            $this->analiseCargoEfetivo = "OK";
        } else {
            $this->analiseCargoEfetivo = "Ainda faltam {$resta3} dias<br/>Somente em {$this->dataCriterioTempoCargo}.";
        }

        # Pontos
        $regraPontos = $this->get_regraPontos(date("Y"));
        $supostoPontos = $this->servidorPontos;
        $this->dataCriterioPontos = $this->get_dataCriterioPontos();

        if ($this->servidorPontos >= $regraPontos) {
            $this->analisePontos = "OK";
        } else {
            # Pega o resto
            $resta4 = $regraPontos - $this->servidorPontos;

            # Verifica se é par
            if (epar($resta4)) {
                $anoFalta = $resta4 / 2;
            }
            $this->analisePontos = "Ainda faltam {$resta4} pontos<br/>Somente em {$this->dataCriterioPontos}.";
        }

        # Data do Direito a Aposentadoria
        $this->dataDireitoAposentadoria = dataMaiorArray([
            $this->dataCriterioIdade,
            $this->dataCriterioTempoContribuicao,
            $this->dataCriterioTempoServicoPublico,
            $this->dataCriterioTempoCargo,
            $this->dataCriterioPontos
        ]);
    }

    ###########################################################

    public function exibeAnalise() {

        # Pega os dados
        $regraPontos = $this->get_regraPontos(date("Y"));

        /*
         *  Tabela
         */

        # Exibe obs para quando o servidor tem tempo celetista
        if ($this->servidorDataIngresso == "09/09/2003") {
            $this->servidorDataIngresso .= " *";
            $mensagem = "* Todo servidor admitido na Uenf como celetista tem a data de ingresso em 09/09/2003.";
        } else {
            $mensagem = null;
        }

        $array = [
            ["Data de Ingresso", $this->dtIngressoDescricao, $this->dtIngresso, $this->servidorDataIngresso, "---", $this->analisaDtIngresso],
            ["Idade", $this->idadeDescricao, "{$this->regraIdade} anos", "{$this->servidorIdade} anos", $this->dataCriterioIdade, $this->analiseIdade],
            ["Contribuição", $this->tempoContribuiçãoDescricao, "{$this->regraContribuicao} anos<br/>(" . ($this->regraContribuicao * 365) . " dias)", intval($this->servidorTempoTotal / 365) . " anos<br/>{$this->servidorTempoTotal} dias", $this->dataCriterioTempoContribuicao, $this->analiseContribuicao],
            ["Pontuação", "Pontuação Atual (" . date("Y") . ")", "{$regraPontos} pontos", "{$this->servidorPontos} pontos", $this->dataCriterioPontos, $this->analisePontos],
            ["Serviço Público", $this->tempoPublicoDescicao, "{$this->servicoPublico} anos<br/>(" . ($this->servicoPublico * 365) . " dias)", "{$this->servidorTempoPublicoIninterrupto} dias", $this->dataCriterioTempoServicoPublico, $this->analisePublico],
            ["Cargo Efetivo", $this->tempoCargoDescicao, "{$this->cargoEfetivo} anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)", "{$this->servidorTempoUenf} dias", $this->dataCriterioTempoCargo, $this->analiseCargoEfetivo]
        ];

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_titulo("Dados");
        $tabela->set_conteudo($array);
        $tabela->set_label(["Item", "Descrição", "Regra", "Servidor", "Data", "Análise"]);
        #$tabela->set_width([15, 20, 15, 15, 15, 20]);
        $tabela->set_align(["left", "left"]);
        $tabela->set_totalRegistro(false);
        $tabela->set_formatacaoCondicional(array(
            array('coluna' => 5,
                'valor' => 'OK',
                'operador' => '=',
                'id' => 'pode'),
            array('coluna' => 5,
                'valor' => "NÃO TEM DIREITO",
                'operador' => '=',
                'id' => 'naoPode'),
            array('coluna' => 5,
                'valor' => 'OK',
                'operador' => '<>',
                'id' => 'podera')
        ));
        $tabela->set_subtitulo($mensagem);
        $tabela->show();
        
    }

    ###########################################################

    public function exibeAnaliseResumo() {

        # Verifica a data limite
        if (jaPassou($this->dataDireitoAposentadoria)) {
            $texto = "O Servidor tem direito a esta modalidade de aposentadoria desde:<br/><b>{$this->dataDireitoAposentadoria}</b>";
            $cor = "success";
        } else {
            $texto = "O Servidor terá direito a esta modalidade de aposentadoria em:<br/><b>{$this->dataDireitoAposentadoria}</b>";
            $cor = "warning";
        }

        # Verifica a regra extra da data de ingresso
        if ($this->analisaDtIngresso == "NÃO TEM DIREITO") {
            $texto = "O Servidor <b>NÃO TEM DIREITO</b><br/>a essa modalidade de aposentadoria.";
            $cor = "alert";
        }

        # Exibe o resumo
        $painel = new Callout($cor);
        $painel->abre();

        p($texto, "center");

        $painel->fecha();
    }

    ###########################################################

    public function getDataAposentadoria() {
        return $this->dataDireitoAposentadoria;
    }

    ###########################################################

    public function getDiasFaltantes() {

        # Verifica se ja passou
        if (jaPassou($this->dataDireitoAposentadoria)) {
            return "0";
        } else {
            return dataDif(date("d/m/Y"), $this->dataDireitoAposentadoria);
        }
    }

    ###########################################################

    public function exibeRegras() {

        $array = [
            ["<p id='pLinha1'>Data de Ingresso</p><p id='pLinha4'>{$this->dtIngressoDescricao}</p>", $this->dtIngresso, $this->dtIngresso],
            ["<p id='pLinha1'>Idade</p><p id='pLinha4'>{$this->idadeDescricao}</p>", $this->idadeMulher . " anos", $this->idadeHomem . " anos"],
            ["<p id='pLinha1'>Contribuição</p><p id='pLinha4'>{$this->tempoContribuiçãoDescricao}</p>", $this->contribuicaoMulher . " anos<br/>(" . ($this->contribuicaoMulher * 365) . " dias)", $this->contribuicaoHomem . " anos<br/>(" . ($this->contribuicaoHomem * 365) . " dias)"],
            ["<p id='pLinha1'>Pontuação Iniciall</p><p id='pLinha4'>{$this->pontuacaoInicialDescricao}</p>", $this->pontosMulher . " pontos", $this->pontosHomem . " pontos"],
            ["<p id='pLinha1'>Serviço Público</p><p id='pLinha4'>{$this->tempoPublicoDescicao}</p>", $this->servicoPublico . " anos<br/>(" . ($this->servicoPublico * 365) . " dias)", $this->servicoPublico . " anos<br/>(" . ($this->servicoPublico * 365) . " dias)"],
            ["<p id='pLinha1'>Cargo Efetivo</p><p id='pLinha4'>{$this->tempoCargoDescicao}</p>", $this->cargoEfetivo . " anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)", $this->cargoEfetivo . " anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)"]
        ];

        $tabela = new Tabela();
        $tabela->set_titulo("Regras Gerais");
        $tabela->set_conteudo($array);
        $tabela->set_label(["Requisito", "Mulher", "Homem"]);
        $tabela->set_width([50, 25, 25]);
        $tabela->set_align(["left"]);
        $tabela->set_totalRegistro(false);
        $tabela->set_rodape("");
        $tabela->show();
    }

    ###########################################################

    public function exibeRemuneração() {

        $array = [
            ["Cálculo Inicial", $this->calculoInicial],
            ["Teto", $this->teto],
            ["Reajuste", $this->reajuste],
            ["Paridade", $this->paridade]
        ];

        $tabela = new Tabela();
        $tabela->set_titulo("Remuneração");
        $tabela->set_conteudo($array);
        $tabela->set_label(["Item", "Descrição"]);
        $tabela->set_width([20, 80]);
        $tabela->set_align(["left", "left"]);
        $tabela->set_totalRegistro(false);
        $tabela->show();
    }

    ###########################################################

    public function exibeResumoCartilha($numero = 1) {

        $figura = new Imagem(PASTA_FIGURAS . "transicaoPontos1{$numero}.png", null, "100%", "100%");
        $figura->set_id('imgCasa');
        $figura->set_class('imagem');
        $figura->show();
    }

    ###########################################################

    public function get_descricao() {

        return $this->descricao;
    }

    ###########################################################

    public function get_dataCriterioPontos() {

        # Define os anos
        $anoInicial = 2022;
        $anoFinal = 2051;
        $anoAtual = date("Y");

        # Pega os pontos
        $pontos = intval($this->servidorIdade + ($this->servidorTempoTotal / 365));
        $pontoAtual = $this->get_regraPontos($anoAtual);

        # Verifica se ja possui a pontuação em 2022
        if ($pontos > $this->get_regraPontos($anoInicial)) {
            return null;
        }

        for ($i = $anoAtual; $i <= $anoFinal; $i++) {
            $pontos += 2;
            $pontosRegra = $this->get_regraPontos($i);
            $resta = $pontosRegra - $pontos;

            # Se alcançou com a data maior
            if ($pontos == $pontosRegra) {

                $data1 = day($this->servidorDataNascimento) . "/" . month($this->servidorDataNascimento) . "/" . $i;
                $data2 = day($this->servidorDataIngresso) . "/" . month($this->servidorDataIngresso) . "/" . $i;
                return dataMaior($data1, $data2);
            }

            # Se alcançou com a data menor
            if ($pontos > $pontosRegra) {

                $data1 = day($this->servidorDataNascimento) . "/" . month($this->servidorDataNascimento) . "/" . $i;
                $data2 = day($this->servidorDataIngresso) . "/" . month($this->servidorDataIngresso) . "/" . $i;
                return dataMenor($data1, $data2);
            }
        }
    }

    ###########################################################

    public function exibeHistoricoPontuacao() {

        # Define os anos
        $anoInicial = 2024;
        $anoFinal = 2051;
        $anoAtual = date("Y");

        # Pega os pontos
        $pontos = intval($this->servidorIdade + ($this->servidorTempoTotal / 365));
        $pontoAtual = $this->get_regraPontos($anoAtual);

        for ($i = $anoAtual; $i <= $anoFinal; $i++) {
            $pontos += 2;
            $pontosRegra = $this->get_regraPontos($i);
            $resta = $pontosRegra - $pontos;

            # Calcula a diferença
            if ($pontosRegra > $pontos) {
                $diferenca = "Ainda faltam {$resta} pontos";
            } else {
                $diferenca = "OK";
            }

            $array[] = [$i, $pontos, $pontosRegra, $diferenca];

            if ($diferenca == "OK") {
                break;
            }
        }

        $tabela = new Tabela();
        $tabela->set_titulo("Histórico da Pontuação");
        $tabela->set_conteudo($array);
        $tabela->set_label(["Ano", "Pontos do Servidor", "Regra", "Diferença"]);
        $tabela->set_width([20, 20, 20, 40]);
        $tabela->set_totalRegistro(false);

        $tabela->set_formatacaoCondicional(array(
            array('coluna' => 3,
                'operador' => '=',
                'valor' => "OK",
                'id' => 'vigente')));
        $tabela->show();
    }

    ###########################################################

    public function exibeGraficoPontuacao() {

        $this->exibeResumoCartilha(4);
    }

    ###########################################################

    private function get_regraPontos($ano = null) {

        # Trata o ano
        if (empty($ano)) {
            return null;
        }

        # Escolhe a tabela masculina
        if ($this->servidorSexo == "Masculino") {

            # Limite máximo
            if ($ano >= 2041) {
                return 105;
            }

            # Limite mínimo
            if ($ano <= 2022) {
                return 96;
            }

            # Busca o valor no array
            foreach ($this->tabelaM as $item) {
                if ($item[0] == $ano) {
                    return $item[1];
                }
            }
        } else {
            # Escolhe a tabela Feminina            
            # Limite máximo
            if ($ano >= 2049) {
                return 100;
            }

            # Limite mínimo
            if ($ano <= 2022) {
                return 86;
            }


            # Busca o valor no array
            foreach ($this->tabelaF as $item) {
                if ($item[0] == $ano) {
                    return $item[1];
                }
            }
        }
    }

    ###########################################################

    public function exibeTabelaRegras() {

        # Escolhe a tabela masculina
        if ($this->servidorSexo == "Masculino") {

            # Limite máximo
//            if ($ano >= 2041) {
//                return 105;
//            }
//
//            # Limite mínimo
//            if ($ano <= 2022) {
//                return 96;
//            }

            $array = $this->tabelaM;
        } else {
            # Escolhe a tabela Feminina            
//            # Limite máximo
//            if ($ano >= 2049) {
//                return 100;
//            }
//
//            # Limite mínimo
//            if ($ano <= 2022) {
//                return 86;
//            }

            $array = $this->tabelaF;
        }

        $tabela = new Tabela();
        $tabela->set_titulo("Regra dos Pontos");
        $tabela->set_subtitulo($this->servidorSexo);
        $tabela->set_conteudo($array);
        $tabela->set_label(["Ano", "Pontos"]);
        $tabela->set_width([50, 50]);
        $tabela->set_totalRegistro(false);
        $tabela->set_rowspan(1);
        $tabela->set_grupoCorColuna(1);
        $tabela->show();
    }

    ###########################################################
}
