<?php

class AposentadoriaTransicaoPontos1 {

    /**
     * Aposentadoria Regras de Transição Pontos 1
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    # Id Servidor
    private $idServidor = null;

    # Descricao
    private $tipo = "Regra de Transição";
    private $descricao = "Aposentadoria por Idade e Tempo de Contribuição<br/>Regra dos Pontos - Integralidade e Paridade";
    private $legislacao = "Artigo 3º da EC nº 90/2021";

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

    # Aposentadoria Compulsoria
    private $dataCompulsoria = null;

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

    # Data da Lei - Só pode aposentar apos essa data
    private $dataLei = "01/01/2022";
    private $ajustado = false;

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

        /*
         *  Pega os dados do servidor
         */
        $pessoal = new Pessoal();

        $this->servidorIdade = $pessoal->get_idade($this->idServidor);
        $this->servidorDataNascimento = $pessoal->get_dataNascimento($this->idServidor);
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
        #$this->servidorPontos = intval($this->servidorIdade + ($this->servidorTempoTotal / 365));

        if ($this->servidorSexo == "Masculino") {
            $this->regraIdade = $this->idadeHomem;
            $this->regraContribuicao = $this->contribuicaoHomem;
        } else {
            $this->regraIdade = $this->idadeMulher;
            $this->regraContribuicao = $this->contribuicaoMulher;
        }

        $hoje = date("d/m/Y");

        # Data da Aposentadoria Compulsoria
        $this->dataCompulsoria = $aposentadoria->get_dataAposentadoriaCompulsoria($this->idServidor);

        /*
         * Data de Ingresso
         */

        if (dataMaior($this->dtIngresso, $this->servidorDataIngresso) == $this->dtIngresso) {
            $this->analisaDtIngresso = "OK";
        } else {
            $this->analisaDtIngresso = "Não Tem Direito";
            $this->temDireito = false;
        }

        /*
         *  Idade
         */

        $this->dataCriterioIdade = addAnos($this->servidorDataNascimento, $this->regraIdade);
        if ($this->servidorIdade >= $this->regraIdade) {
            $this->analiseIdade = "OK";
        } else {
            # Calcula a data
            $this->analiseIdade = "Ainda faltam<br/>" . dataDif(date("d/m/Y"), $this->dataCriterioIdade) . " dias.";
        }

        /*
         *  Tempo de Contribuição
         */
        $resta1 = ($this->regraContribuicao * 365) - $this->servidorTempoTotal;
        $this->dataCriterioTempoContribuicao = addDias($hoje, $resta1, false);  // retiro a contagem do primeiro dia para não contar hoje 2 vezes
        if ($this->servidorTempoTotal >= ($this->regraContribuicao * 365)) {
            $this->analiseContribuicao = "OK";
        } else {
            $this->analiseContribuicao = "Ainda faltam<br/>{$resta1} dias.";
        }

        /*
         *  Serviço Público Initerrupto
         */
        $resta2 = ($this->servicoPublico * 365) - $this->servidorTempoPublicoIninterrupto;
        $this->dataCriterioTempoServicoPublico = addDias($hoje, $resta2, false);  // retiro a contagem do primeiro dia para não contar hoje 2 vezes
        if ($this->servidorTempoPublicoIninterrupto >= ($this->servicoPublico * 365)) {
            $this->analisePublico = "OK";
        } else {
            $this->analisePublico = "Ainda faltam<br/>{$resta2} dias.";
        }

        /*
         *  Cargo Efetivo
         */
        $resta3 = ($this->cargoEfetivo * 365) - $this->servidorTempoUenf;
        $this->dataCriterioTempoCargo = addDias($hoje, $resta3, false);  // retiro a contagem do primeiro dia para não contar hoje 2 vezes
        if ($this->servidorTempoUenf >= ($this->cargoEfetivo * 365)) {
            $this->analiseCargoEfetivo = "OK";
        } else {
            $this->analiseCargoEfetivo = "Ainda faltam<br/>{$resta3} dias.";
        }

        /*
         *  Pontos
         */

        # Define os anos
        $anoFinal = 2051;
        $anoAtual = date("Y");

        # Calcula a data do critério de pontos
        for ($i = $anoAtual; $i <= $anoFinal; $i++) {

            # Pega os pontos da regra para o ano $i
            $pontosRegra = $this->get_regraPontos($i);

            # Pega os pontos possíveis nesse mesmo ano
            $pontosPossíveis = $this->get_pontoPossivel($i);

            # Pega os Pontos Atuais
            $this->servidorPontos = $this->get_pontoAtual();

            # Se alcançou com a data maior
            if ($pontosPossíveis == $pontosRegra) {

                # Data do aniversário
                $data1 = day($this->servidorDataNascimento) . "/" . month($this->servidorDataNascimento) . "/" . $i;

                # Data do tempo de contribuicao
                $diasContribuicao = $this->get_contribuicaoPosivel($i) * 365;
                $diasqueResta = $diasContribuicao - $this->servidorTempoTotal;
                $data2 = addDias($hoje, $diasqueResta, false);  // retiro a contagem do primeiro dia para não contar hoje 2 vezes
                # Verifica o mais distante.
                $this->dataCriterioPontos = dataMaior($data1, $data2);
                break;
            }

            # Se alcançou com a data menor
            if ($pontosPossíveis > $pontosRegra) {

                # Data do aniversário
                $data1 = day($this->servidorDataNascimento) . "/" . month($this->servidorDataNascimento) . "/" . $i;

                # Data do tempo de contribuicao
                $diasContribuicao = $this->get_contribuicaoPosivel($i) * 365;
                $diasqueResta = $diasContribuicao - $this->servidorTempoTotal;
                $data2 = addDias($hoje, $diasqueResta, false);  // retiro a contagem do primeiro dia para não contar hoje 2 vezes

                $this->dataCriterioPontos = dataMenor($data1, $data2);
                break;
            }
        }

        if ($this->servidorPontos >= $this->get_regraPontos(date("Y"))) {
            $this->analisePontos = "OK";
        } else {
            # Pega o resto
            $resta4 = $this->get_regraPontos(date("Y")) - $this->servidorPontos;
            $this->analisePontos = "Ainda faltam<br/>{$resta4} pontos.";
        }

        #####

        /*
         *  Data do Direito a Aposentadoria
         */
        $this->dataDireitoAposentadoria = dataMaiorArray([
            $this->dataCriterioIdade,
            $this->dataCriterioTempoContribuicao,
            $this->dataCriterioTempoServicoPublico,
            $this->dataCriterioTempoCargo,
            $this->dataCriterioPontos
        ]);

        # Ajusta a data quando for antes da data da Lei
        if (dataMaior($this->dataDireitoAposentadoria, $this->dataLei) == $this->dataLei) {
            $this->dataDireitoAposentadoria = $this->dataLei;
            $this->ajustado = true;
        }

        # Define o texto de retorno  
        if (jaPassou($this->dataDireitoAposentadoria)) {
            $this->textoRetorno = "O Servidor tem direito a esta modalidade de aposentadoria desde:<br/><b>{$this->dataDireitoAposentadoria}</b>";
            $this->textoReduzido = "Desde:<br/><b>{$this->dataDireitoAposentadoria}</b>";
            $this->corFundo = "success";
            $this->temDireito = true;
        } else {
            $this->textoRetorno = "O Servidor terá direito a esta modalidade de aposentadoria em:<br/><b>{$this->dataDireitoAposentadoria}</b>";
            $this->textoReduzido = "Somente em:<br/><b>{$this->dataDireitoAposentadoria}</b>";
            $this->corFundo = "warning";
            $this->temDireito = true;
        }

        # Verifica a regra extra da data de ingresso
        if ($this->analisaDtIngresso == "Não Tem Direito") {
            $this->textoRetorno = "O Servidor <b>Não Tem Direito</b><br/>a essa modalidade de aposentadoria.";
            $this->textoReduzido = "<b>Não Tem Direito</b>";
            $this->corFundo = "alert";
            $this->temDireito = false;
        }

        # Compara com a data da compulsória
        if ($this->temDireito) {
            if (dataMaior($this->dataDireitoAposentadoria, $this->dataCompulsoria) == $this->dataDireitoAposentadoria) {
                $this->textoRetorno = "O Servidor <b>Não Tem Direito</b><br/>a essa modalidade de aposentadoria.";
                $this->textoReduzido = "<b>Não Tem Direito</b>";
                $this->corFundo = "alert";
                $this->temDireito = false;
            }
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
            ["Data de Ingresso",
                $this->dtIngressoDescricao,
                $this->dtIngresso,
                $this->servidorDataIngresso,
                "---",
                $this->analisaDtIngresso],
            ["Idade",
                $this->idadeDescricao,
                "{$this->regraIdade} anos",
                "{$this->servidorIdade} anos<br/>({$this->servidorDataNascimento})",
                $this->dataCriterioIdade,
                $this->analiseIdade],
            ["Contribuição",
                $this->tempoContribuiçãoDescricao,
                "{$this->regraContribuicao} anos<br/>(" . ($this->regraContribuicao * 365) . " dias)",
                intval($this->servidorTempoTotal / 365) . " anos<br/>({$this->servidorTempoTotal} dias)",
                $this->dataCriterioTempoContribuicao,
                $this->analiseContribuicao],
            ["Pontuação",
                "Pontuação Atual (" . date("Y") . ")",
                "{$regraPontos} pontos",
                "{$this->servidorPontos} pontos<br/>({$this->servidorIdade} + " . intval($this->servidorTempoTotal / 365) . ")",
                $this->dataCriterioPontos,
                $this->analisePontos],
            ["Serviço Público",
                $this->tempoPublicoDescicao,
                "{$this->servicoPublico} anos<br/>(" . ($this->servicoPublico * 365) . " dias)",
                "{$this->servidorTempoPublicoIninterrupto} dias",
                $this->dataCriterioTempoServicoPublico,
                $this->analisePublico],
            ["Cargo Efetivo",
                $this->tempoCargoDescicao,
                "{$this->cargoEfetivo} anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)",
                "{$this->servidorTempoUenf} dias",
                $this->dataCriterioTempoCargo,
                $this->analiseCargoEfetivo]
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

        # Verifica a compulsória
        if (dataMaior($this->dataDireitoAposentadoria, $this->dataCompulsoria) == $this->dataDireitoAposentadoria) {
            $msgCompulsoria = "O Servidor <b>Não Tem Direito</b> a essa modalidade de aposentadoria, pois a data em que alcançaria o direito é posterior a {$this->dataCompulsoria}, data da aposentadoria compulsória.";
            if ($relatorio) {
                p($msgCompulsoria, "left", "f12");
            } else {
                callout($msgCompulsoria, "alert");
            }
        }

        # Verifica se a data da aposentadoria 
        if ($this->ajustado) {
            $msgAjustado = "A data da aposentadoria foi ajustada para {$this->dataLei}, pois, nessa modalidade de aposentadoria, a data não pode ser anterior a data da Lei Complementar nº 195/2021";
            if ($relatorio) {
                tituloRelatorio("Atenção");
                $painel = new Callout("secondary");
                $painel->abre();
                p($msgAjustado, "left", "f12");
                $painel->fecha();
            } else {
                tituloTable("Atenção");
                $painel = new Callout("warning");
                $painel->abre();
                p($msgAjustado, "center");
                $painel->fecha();
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

        # Faz a análise
        if (!empty($idServidor)) {
            $this->fazAnalise($idServidor);
        }

        # Verifica se tem direito
        if ($this->temDireito) {
            return $this->dataDireitoAposentadoria;
        } else {
            return "---";
        }
    }

    ###########################################################

    public function getDiasFaltantes($idServidor = null) {

        if (!empty($idServidor)) {
            $this->fazAnalise($idServidor);
        }

        # Verifica se tem direito
        if ($this->temDireito) {
            # Verifica se ja passou
            if (jaPassou($this->dataDireitoAposentadoria)) {
                return "0";
            } else {
                return dataDif(date("d/m/Y"), $this->dataDireitoAposentadoria);
            }
        } else {
            return "Não Tem Direito";
        }
    }

    ###########################################################

    public function exibeRegras($relatorio = false) {

        $array = [
            ["<p id='pLinha1'>Data de Ingresso</p><p id='pLinha4'>{$this->dtIngressoDescricao}</p>", $this->dtIngresso, $this->dtIngresso],
            ["<p id='pLinha1'>Idade</p><p id='pLinha4'>{$this->idadeDescricao}</p>", $this->idadeMulher . " anos", $this->idadeHomem . " anos"],
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

        $figura = new Imagem(PASTA_FIGURAS . "transicaoPontos1{$numero}.png", null, "100%", "100%");
        $figura->set_id('imgCasa');
        $figura->set_class('imagem');
        $figura->show();
    }

    ###########################################################

    public function exibeHistoricoPontuacao($relatorio = false) {

        # Define os anos
        $anoInicial = 2024;
        $anoFinal = 2051;
        $anoAtual = date("Y");
        $anoNascimento = year($this->servidorDataNascimento);

        $aposentadoria = new Aposentadoria();
        $anoIngresso = year($aposentadoria->get_dtIngresso($this->idServidor));

        for ($i = $anoAtual; $i <= $anoFinal; $i++) {

            # Pega os dados
            $pontos = $this->get_pontoPossivel($i);
            $pontosRegra = $this->get_regraPontos($i);
            $resta = $pontosRegra - $pontos;
            $demostrativo = $this->get_demonstrativoCalculoPontoPossivel($i);

            # Calcula a diferença
            if ($pontosRegra > $pontos) {
                $diferenca = "Ainda faltam {$resta} pontos";
            } else {
                $diferenca = "OK";
            }

            $array[] = [$i, $demostrativo, $pontos, $pontosRegra, $diferenca];

            if ($diferenca == "OK") {
                break;
            }
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
        $tabela->set_label(["Ano", "Cálculo<br/>Idade + Tempo", "Pontos do Servidor", "Regra", "Diferença"]);
        $tabela->set_width([18, 18, 18, 18, 28]);
        $tabela->set_totalRegistro(false);

        if (!$relatorio) {
            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 4,
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
        $link = "?fase=carregarPagina&id={$idServidor}&link=pontosIntegral";

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

    public function get_pontoPossivel($ano) {
        /*
         * Informa o ponto possível no ano informado
         */

        # Pega o ano de Nascimento
        $anoNascimento = year($this->servidorDataNascimento);

        # Pega o tempo de contribuição hoje
        $tempoContribuicaoHoje = $this->servidorTempoTotal;

        # Soma com os dias possíveis do ano indicado
        $tempoPossivel = getNumDias(date("d/m/Y"), "31/12/{$ano}");

        # Passa para ano a soma dos tempos
        $tempo = intval(($tempoContribuicaoHoje + $tempoPossivel) / 365);

        return($ano - $anoNascimento) + $tempo;
    }

    ###########################################################

    public function get_demonstrativoCalculoPontoPossivel($ano) {
        /*
         * Exibe o demostrativo fde cálculo do ponto possível no ano intormado
         */

        # Pega o ano de Nascimento
        $anoNascimento = year($this->servidorDataNascimento);

        # Pega o tempo de contribuição hoje
        $tempoContribuicaoHoje = $this->servidorTempoTotal;

        # Soma com os dias possíveis do ano indicado
        $tempoPossivel = getNumDias(date("d/m/Y"), "31/12/{$ano}");

        # Passa para ano a soma dos tempos
        $tempo = intval(($tempoContribuicaoHoje + $tempoPossivel) / 365);

        # Idade
        $idade = $ano - $anoNascimento;

        return "{$idade} + {$tempo}";
    }

    ###########################################################

    public function get_contribuicaoPosivel($ano) {

        /*
         * Informa a contribuição Possível do ano informado
         */

        # Pega o ano de Nascimento
        $anoNascimento = year($this->servidorDataNascimento);

        # Pega o tempo de contribuição hoje
        $tempoContribuicaoHoje = $this->servidorTempoTotal;

        # Soma com os dias possíveis do ano indicado
        $tempoPossivel = getNumDias(date("d/m/Y"), "31/12/{$ano}");

        # Passa para ano a soma dos tempos
        $tempo = intval(($tempoContribuicaoHoje + $tempoPossivel) / 365);

        return $tempo;
    }

    ###########################################################

    public function get_pontoAtual() {

        return intval($this->servidorIdade + ($this->servidorTempoTotal / 365));
    }

    ###########################################################
}
