<?php

class AposentadoriaTransicaoPontos2 {

    /**
     * Aposentadoria Regras de Transição Pontos 1
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    # Id Servidor
    private $idServidor = null;

    # Descricao
    private $tipo = "Regra de Transição";
    private $descricao = "Aposentadoria por Idade e Tempo de Contribuição<br/>Regra dos Pontos - Média";
    private $legislacao = "Artigo 3º da EC nº 90/2021";

    # Regras
    private $dataDivisorIdade = "01/01/2025";
    private $idadeHomemAntes = 61;
    private $idadeHomemDepois = 62;
    private $idadeMulherAntes = 56;
    private $idadeMulherDepois = 57;
    private $dtIngresso = "31/12/2021";
    private $contribuicaoHomem = 35;
    private $contribuicaoMulher = 30;
    private $servicoPublico = 20;
    private $cargoEfetivo = 5;
    private $pontosHomem = 90;
    private $pontosMulher = 86;
    private $regraIdade = null;
    private $regraContribuicao = null;

    # Remuneração
    private $calculoInicial = "Média aritmética simples das 80% maiores remunerações a partir de julho de 1994";
    private $teto = "Remuneração do servidor no cargo efetivo";
    private $reajuste = "INPC - Lei 6.2442012";
    private $paridade = "SEM PARIDADE";

    # Descrições
    private $dtIngressoDescricao = "Data de ingresso no serviço público sem interrupção.";
    private $tempoContribuiçãoDescricao = "Tempo Total averbado<br/>(público e privado).";
    private $pontuacaoInicialDescricao = "Pontuação Inicial.";
    private $idadeDescricao = "Idade do servidor.";
    private $tempoPublicoDescicao = "Tempo de todos os periodo públicos ininterruptos.";
    private $tempoCargoDescicao = "Tempo no mesmo órgão e mesmo cargo.";
    private $dtRequesitosCumpridosDescicao = "Data limite para o cumprimento dos requesito.";

    # Dados do Servidor
    private $servidorDataNascimento = null;
    private $servidorIdade = null;
    private $servidorSexo = null;
    private $serviçoTempoTotal = null;
    private $servidorDataIngresso = null;
    private $servidorPontos = null;

    # Tempo do Servidor
    private $servidorTempoAverbadoPublico = null;
    private $servidorTempoAverbadoPrivado = null;
    private $servidorTempoUenf = null;
    private $servidorTempoTotal = null;
    private $servidorTempoPublicoIninterrupto = null;

    # Analises
    private $analisaDtIngresso = null;
    private $analiseIdade = null;
    private $analiseContribuicao = null;
    private $analisePublico = null;
    private $analiseCargoEfetivo = null;
    private $analiseDtRequesitosCumpridos = null;
    private $analisePontos = null;

    # Variaveis de Retorno    
    private $dataCriterioIngresso = null;
    private $dataCriterioIdade = null;
    private $dataCriterioPontos = null;
    private $dataCriterioTempoContribuicao = null;
    private $dataCriterioTempoServicoPublico = null;
    private $dataCriterioTempoCargo = null;
    private $dataDireitoAposentadoria = null;
    private $temDireito = true;
    private $textoRetorno = null;
    private $textoReduzido = null;
    private $corFundo = null;

    ###########################################################
    # Tabela de Pontos
    private $tabelaM = [
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
    private $tabelaF = [
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

    public function __construct($idServidor = null) {

        if (!empty($idServidor)) {
            $this->fazAnalise($idServidor);
        }
    }

    ###########################################################    

    public function fazAnalise($idServidor) {
        if (empty($idServidor)) {
            alert("O idServidor não foi Informado");
        } else {
            $this->idServidor = $idServidor;
        }

        # Inicializa a flag
        $this->temDireito = true;

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

        # Idade
        if ($this->servidorSexo == "Masculino") {
            $this->regraContribuicao = $this->contribuicaoHomem;
            $this->regraIdade = $this->idadeHomemAntes;

            # Calcula a data
            $this->dataCriterioIdade = addAnos($this->servidorDataNascimento, $this->regraIdade);

            # Verifica se é antes a data divisor de idade
            if (year($this->dataDivisorIdade) < year($this->dataCriterioIdade)) {
                $this->regraIdade = $this->idadeHomemDepois;
                $this->dataCriterioIdade = addAnos($this->servidorDataNascimento, $this->regraIdade);
            }
        } else {
            $this->regraContribuicao = $this->contribuicaoMulher;
            $this->regraIdade = $this->idadeMulherAntes;

            # Calcula a data
            $this->dataCriterioIdade = addAnos($this->servidorDataNascimento, $this->regraIdade);

            # Verifica se é antes a data divisor de idade
            if (year($this->dataDivisorIdade) < year($this->dataCriterioIdade)) {
                $this->regraIdade = $this->idadeMulherDepois;
                $this->dataCriterioIdade = addAnos($this->servidorDataNascimento, $this->regraIdade);
            }
        }

        $hoje = date("d/m/Y");

        /*
         * Análise
         */

        # Data de Ingresso        
        if (dataMaior($this->dtIngresso, $this->servidorDataIngresso) == $this->dtIngresso) {
            $this->analisaDtIngresso = "OK";
        } else {
            $this->analisaDtIngresso = "Não Tem Direito";
        }

        # Idade
        if ($this->servidorIdade >= $this->regraIdade) {
            $this->analiseIdade = "OK";
        } else {
            # Calcula a data
            $this->analiseIdade = "Ainda faltam<br/>" . dataDif(date("d/m/Y"), $this->dataCriterioIdade) . " dias.";
        }

        # Tempo de Contribuição
        $resta1 = ($this->regraContribuicao * 365) - $this->servidorTempoTotal;
        $this->dataCriterioTempoContribuicao = addDias($hoje, $resta1, false);  // retiro a contagem do primeiro dia para não contar hoje 2 vezes
        if ($this->servidorTempoTotal >= ($this->regraContribuicao * 365)) {
            $this->analiseContribuicao = "OK";
        } else {
            $this->analiseContribuicao = "Ainda faltam<br/>{$resta1} dias.";
        }

        # Serviço Público Initerrupto
        $resta2 = ($this->servicoPublico * 365) - $this->servidorTempoPublicoIninterrupto;
        $this->dataCriterioTempoServicoPublico = addDias($hoje, $resta2, false);  // retiro a contagem do primeiro dia para não contar hoje 2 vezes
        if ($this->servidorTempoPublicoIninterrupto >= ($this->servicoPublico * 365)) {
            $this->analisePublico = "OK";
        } else {
            $this->analisePublico = "Ainda faltam<br/>{$resta2} dias.";
        }

        # Cargo Efetivo
        $resta3 = ($this->cargoEfetivo * 365) - $this->servidorTempoUenf;
        $this->dataCriterioTempoCargo = addDias($hoje, $resta3, false);  // retiro a contagem do primeiro dia para não contar hoje 2 vezes
        if ($this->servidorTempoUenf >= ($this->cargoEfetivo * 365)) {
            $this->analiseCargoEfetivo = "OK";
        } else {
            $this->analiseCargoEfetivo = "Ainda faltam<br/>{$resta3} dias.";
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
            $this->analisePontos = "Ainda faltam<br/>{$resta4} pontos.";
        }

        # Data do Direito a Aposentadoria
        $this->dataDireitoAposentadoria = dataMaiorArray([
            $this->dataCriterioIdade,
            $this->dataCriterioTempoContribuicao,
            $this->dataCriterioTempoServicoPublico,
            $this->dataCriterioTempoCargo,
            $this->dataCriterioPontos
        ]);

        # Define o texto de retorno  
        if (jaPassou($this->dataDireitoAposentadoria)) {
            $this->textoRetorno = "O Servidor tem direito a esta modalidade de aposentadoria desde:<br/><b>{$this->dataDireitoAposentadoria}</b>";
            $this->textoReduzido = "Desde:<br/><b>{$this->dataDireitoAposentadoria}</b>";
            $this->corFundo = "success";
        } else {
            $this->textoRetorno = "O Servidor terá direito a esta modalidade de aposentadoria em:<br/><b>{$this->dataDireitoAposentadoria}</b>";
            $this->textoReduzido = "Somente em:<br/><b>{$this->dataDireitoAposentadoria}</b>";
            $this->corFundo = "warning";
        }

        # Verifica a regra extra da data de ingresso
        if ($this->analisaDtIngresso == "Não Tem Direito") {
            $this->textoRetorno = "O Servidor <b>Não Tem Direito</b><br/>a essa modalidade de aposentadoria.";
            $this->textoReduzido = "<b>Não Tem Direito</b>";
            $this->corFundo = "alert";
        }
    }

    ###########################################################

    public function exibeAnalise($relatorio = false) {

        # Pega os dados
        $regraPontos = $this->get_regraPontos(date("Y"));

        /*
         *  Tabela
         */

        # Exibe obs para quando o servidor tem tempo celetista
        if ($this->servidorDataIngresso == "09/09/2003") {
            $this->servidorDataIngresso .= " *";
            $mensagem = "* O Rio Previdência considera, para definição da data de ingresso no serviço público, somente o tempo como estatutário.<br/>"
                    . "Dessa forma, todo servidor, admitido na Uenf antes de 09/09/2003, como celetista, tem considerada a data 09/09/2003 como a de ingresso no serviço público.";
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
        if ($relatorio) {
            tituloRelatorio("Dados");
            $tabela = new Relatorio();
            $tabela->set_cabecalhoRelatorio(false);
            $tabela->set_menuRelatorio(false);
            $tabela->set_totalRegistro(false);
            $tabela->set_dataImpressao(false);
            $tabela->set_bordaInterna(true);
            $tabela->set_log(false);
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo("Dados");
        }

        $tabela->set_conteudo($array);
        $tabela->set_label(["Item", "Descrição", "Regra", "Servidor", "Data", "Análise"]);
        $tabela->set_width([14, 30, 14, 14, 14, 14]);
        $tabela->set_align(["left", "left"]);
        $tabela->set_totalRegistro(false);

        if (!$relatorio) {
            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 5,
                    'valor' => 'OK',
                    'operador' => '=',
                    'id' => 'pode'),
                array('coluna' => 5,
                    'valor' => "Não Tem Direito",
                    'operador' => '=',
                    'id' => 'naoPode'),
                array('coluna' => 5,
                    'valor' => 'OK',
                    'operador' => '<>',
                    'id' => 'podera')
            ));
        }
        $tabela->show();

        # Mensagem
        if (!empty($mensagem)) {
            if ($relatorio) {
                p($mensagem, "left", "f12");
            } else {
                callout($mensagem);
            }
        }
    }

    ###########################################################

    public function exibeAnaliseResumo($relatorio = false) {

        # Exibe o resumo
        if ($relatorio) {
            return $this->textoRetorno;
        } else {
            $painel = new Callout($this->corFundo);
            $painel->abre();
            p($this->textoRetorno, "center");
            $painel->fecha();
        }
    }

    ###########################################################

    public function getDataAposentadoria($idServidor = null) {

        if (!empty($idServidor)) {
            $this->fazAnalise($idServidor);
        }

        return $this->dataDireitoAposentadoria;
    }

    ###########################################################

    public function getDiasFaltantes($idServidor = null) {

        if (!empty($idServidor)) {
            $this->fazAnalise($idServidor);
        }

        # Verifica se ja passou
        if (jaPassou($this->dataDireitoAposentadoria)) {
            return "0";
        } else {
            return dataDif(date("d/m/Y"), $this->dataDireitoAposentadoria);
        }
    }

    ###########################################################

    public function exibeRegras($relatorio = false) {

        $array = [
            ["<p id='pLinha1'>Data de Ingresso</p><p id='pLinha4'>{$this->dtIngressoDescricao}</p>", $this->dtIngresso, $this->dtIngresso],
            ["<p id='pLinha1'>Idade<br/>Antes de {$this->dataDivisorIdade}</p><hr/ id='geral'><p id='pLinha1'>Depois de {$this->dataDivisorIdade}</p><p id='pLinha4'>{$this->idadeDescricao}</p>", "{$this->idadeMulherAntes} anos<hr/ id='geral'>{$this->idadeMulherDepois} anos<br/>", "{$this->idadeHomemAntes} anos<hr/ id='geral'>{$this->idadeHomemDepois} anos<br/>"],
            ["<p id='pLinha1'>Contribuição</p><p id='pLinha4'>{$this->tempoContribuiçãoDescricao}</p>", $this->contribuicaoMulher . " anos<br/>(" . ($this->contribuicaoMulher * 365) . " dias)", $this->contribuicaoHomem . " anos<br/>(" . ($this->contribuicaoHomem * 365) . " dias)"],
            ["<p id='pLinha1'>Pontuação Iniciall</p><p id='pLinha4'>{$this->pontuacaoInicialDescricao}</p>", $this->pontosMulher . " pontos", $this->pontosHomem . " pontos"],
            ["<p id='pLinha1'>Serviço Público</p><p id='pLinha4'>{$this->tempoPublicoDescicao}</p>", $this->servicoPublico . " anos<br/>(" . ($this->servicoPublico * 365) . " dias)", $this->servicoPublico . " anos<br/>(" . ($this->servicoPublico * 365) . " dias)"],
            ["<p id='pLinha1'>Cargo Efetivo</p><p id='pLinha4'>{$this->tempoCargoDescicao}</p>", $this->cargoEfetivo . " anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)", $this->cargoEfetivo . " anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)"]
        ];

        # Exibe a tabela
        if ($relatorio) {
            tituloRelatorio("Regras Gerais");
            $tabela = new Relatorio();
            $tabela->set_cabecalhoRelatorio(false);
            $tabela->set_menuRelatorio(false);
            $tabela->set_totalRegistro(false);
            $tabela->set_dataImpressao(false);
            $tabela->set_bordaInterna(true);
            $tabela->set_log(false);
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo("Regras Gerais");
        }

        $tabela->set_conteudo($array);
        $tabela->set_label(["Requisito", "Mulher", "Homem"]);
        $tabela->set_width([50, 25, 25]);
        $tabela->set_align(["left"]);
        $tabela->set_totalRegistro(false);
        $tabela->set_rodape("");
        $tabela->show();
    }

    ###########################################################

    public function exibeRemuneração($relatorio = false) {

        $array = [
            ["Cálculo Inicial", $this->calculoInicial],
            ["Teto", $this->teto],
            ["Reajuste", $this->reajuste],
            ["Paridade", $this->paridade]
        ];

        # Exibe a tabela
        if ($relatorio) {
            tituloRelatorio("Remuneração");
            $tabela = new Relatorio();
            $tabela->set_cabecalhoRelatorio(false);
            $tabela->set_menuRelatorio(false);
            $tabela->set_totalRegistro(false);
            $tabela->set_dataImpressao(false);
            $tabela->set_bordaInterna(true);
            $tabela->set_log(false);
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo("Remuneração");
        }

        $tabela->set_conteudo($array);
        $tabela->set_label(["Item", "Descrição"]);
        $tabela->set_width([20, 80]);
        $tabela->set_align(["left", "left"]);
        $tabela->set_totalRegistro(false);
        $tabela->show();
    }

    ###########################################################

    public function exibeResumoCartilha($numero = 1) {

        $figura = new Imagem(PASTA_FIGURAS . "transicaoPontos2{$numero}.png", null, "100%", "100%");
        $figura->set_id('imgCasa');
        $figura->set_class('imagem');
        $figura->show();
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

            $pontos += 2;
        }
    }

    ###########################################################

    public function exibeHistoricoPontuacao($relatorio = false) {

        # Define os anos
        $anoInicial = 2024;
        $anoFinal = 2051;
        $anoAtual = date("Y");

        # Pega os pontos
        $pontos = intval($this->servidorIdade + ($this->servidorTempoTotal / 365));
        $pontoAtual = $this->get_regraPontos($anoAtual);

        for ($i = $anoAtual; $i <= $anoFinal; $i++) {

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

            $pontos += 2;
        }

        # Exibe a tabela
        if ($relatorio) {
            tituloRelatorio("Histórico da Pontuação");
            $tabela = new Relatorio();
            $tabela->set_cabecalhoRelatorio(false);
            $tabela->set_menuRelatorio(false);
            $tabela->set_totalRegistro(false);
            $tabela->set_dataImpressao(false);
            $tabela->set_bordaInterna(true);
            $tabela->set_log(false);
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo("Histórico da Pontuação");
            $tabela->set_subtitulo("(A cada ano o servidor aumenta 2 pontos, a cada 2 anos a regra aumenta 1 ponto)");
        }

        $tabela->set_conteudo($array);
        $tabela->set_label(["Ano", "Pontos do Servidor", "Regra", "Diferença"]);
        $tabela->set_width([25, 25, 25, 25]);
        $tabela->set_totalRegistro(false);

        if (!$relatorio) {
            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 3,
                    'operador' => '=',
                    'valor' => "OK",
                    'id' => 'vigente')));
        }
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

    public function exibeTabelaRegras($relatorio = false) {

        $grid = new Grid();
        $grid->abreColuna(12);

        tituloTable("Regra dos Pontos");

        $grid->fechaColuna();
        $grid->abreColuna(6);

        # Exibe a tabela Masculina
        if ($relatorio) {

            $tabela = new Relatorio();
            tituloRelatorio("Masculino");
            $tabela->set_cabecalhoRelatorio(false);
            $tabela->set_menuRelatorio(false);
            $tabela->set_totalRegistro(false);
            $tabela->set_dataImpressao(false);
            $tabela->set_bordaInterna(true);
            $tabela->set_log(false);
        } else {
            ;
            $tabela = new Tabela();
            $tabela->set_titulo("Masculino");
        }

        $tabela->set_conteudo($this->tabelaM);
        $tabela->set_label(["Ano", "Pontos"]);
        $tabela->set_width([50, 50]);
        $tabela->set_totalRegistro(false);
        $tabela->show();

        $grid->fechaColuna();
        $grid->abreColuna(6);

        # Exibe a tabela Masculina
        if ($relatorio) {
            tituloRelatorio("Feminino");
            $tabela = new Relatorio();
            $tabela->set_cabecalhoRelatorio(false);
            $tabela->set_menuRelatorio(false);
            $tabela->set_totalRegistro(false);
            $tabela->set_dataImpressao(false);
            $tabela->set_bordaInterna(true);
            $tabela->set_log(false);
        } else {
            ;
            $tabela = new Tabela();
            $tabela->set_titulo("Feminino");
        }

        $tabela->set_conteudo($this->tabelaF);
        $tabela->set_label(["Ano", "Pontos"]);
        $tabela->set_width([50, 50]);
        $tabela->set_totalRegistro(false);
        $tabela->show();

        $grid->fechaColuna();
        $grid->fechaGrid();
    }

    ###########################################################

    public function get_descricao() {

        return $this->descricao;
    }

    ###########################################################

    public function get_tipo() {

        return $this->tipo;
    }

    ###########################################################

    public function get_legislacao() {

        return $this->legislacao;
    }

   ###########################################################

    public function exibeAnaliseTabela($idServidor) {

        # Faz a análise
        $this->fazAnalise($idServidor);
        
        # Define o link
        $link = "?fase=carregarPagina&id={$idServidor}&link=pontosMedia";
        
        echo "<a href='{$link}'>";

        # Exibe o resumo
        $painel = new Callout($this->corFundo);
        $painel->abre();
        p($this->textoReduzido, "center");
        $painel->fecha();
        
        echo "</a>";
    }

    ###########################################################

    public function get_textoReduzido($idServidor) {
        
        # Faz a análise
        $this->fazAnalise($idServidor);
        
        # Retorna
        return $this->textoReduzido;
    }

    ###########################################################
}
