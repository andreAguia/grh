<?php

class AposentadoriaTransicaoPedagio3 {

    /**
     * Aposentadoria Regras de Transição Pedagio 1
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    # Id Servidor
    private $idServidor = null;

    # Descricao
    private $tipo = "Regra de Transição";
    private $descricao = "Aposentadoria por Idade e Tempo de Contribuição<br/>Regra do Pedágio com Redutor de Idade - Integralidade e Paridade";
    private $legislacao = "§5º do artigo 4º da EC nº 90/2021.";

    # Regras
    private $idadeHomem = 60;
    private $idadeMulher = 55;
    private $dtIngresso = "16/12/1998";
    private $contribuicaoHomem = 35;
    private $contribuicaoMulher = 30;
    private $servicoPublico = 20;
    private $cargoEfetivo = 5;
    private $pedagio = 20;
    private $regraIdade = null;
    private $regraContribuicao = null;

    # Remuneração
    private $calculoInicial = "Última remuneração";
    private $teto = "Remuneração do servidor no cargo efetivo";
    private $reajuste = "Na mesma data e índice dos servidores ativos";
    private $paridade = "COM PARIDADE";

    # Descrições
    private $dtIngressoDescricao = "Data de ingresso no serviço público sem interrupção.";
    private $tempoContribuiçãoDescricao = "Tempo Total averbado<br/>(público e privado).";
    private $pedagioDescricao = "Período adicional de contribuição calculado apartir do tempo de contribuição que faltava ao servidor em 01/01/2021";
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
    private $servidorTempoAntes31_12_2021 = null;
    private $servidorTempoSobra = null;
    private $servidorPedagio = null;

    # Tempo do Servidor
    private $servidorTempoAverbadoPublico = null;
    private $servidorTempoAverbadoPrivado = null;
    private $servidorTempoUenf = null;
    private $servidorTempoTotal = null;
    private $servidorTempoPublicoIninterrupto = null;

    # Redutor    
    private $tempoExcedente = null;
    private $diasIdadeQueFalta = null;
    private $mesesIdadeQueFalta = null;
    private $diasParaPagar = null;
    private $mesesParaPagar = null;
    private $mensagemRedutor = null;

    # Analises
    private $analisaDtIngresso = null;
    private $analiseIdade = null;
    private $analiseContribuicao = null;
    private $analisePublico = null;
    private $analiseCargoEfetivo = null;
    private $analiseDtRequesitosCumpridos = null;
    private $analisePedagio = null;
    private $analiseReducao = null;

    # Variaveis de Retorno    
    private $dataCriterioIngresso = null;
    private $dataCriterioIdade = null;
    private $dataCriterioPedagio = null;
    private $dataCriterioTempoContribuicao = null;
    private $dataCriterioTempoServicoPublico = null;
    private $dataCriterioTempoCargo = null;
    private $dataCriterioRedutor = null;
    private $dataDireitoAposentadoria = null;
    private $temDireito = true;
    private $textoRetorno = null;
    private $textoReduzido = null;
    private $corFundo = null;

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

        /*
         *  Data de Ingresso        
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
            $this->analiseIdade = "Ainda faltam<br/>" . dataDif(date("d/m/Y"), $this->dataCriterioIdade) . " dias.<hr id='geral' />Somente em {$this->dataCriterioIdade}.";
        }

        /*
         *  Tempo de Contribuição
         */
        $resta1 = ($this->regraContribuicao * 365) - $this->servidorTempoTotal;
        $this->dataCriterioTempoContribuicao = addDias($hoje, $resta1, false);  // retiro a contagem do primeiro dia para não contar hoje 2 vezes
        if ($this->servidorTempoTotal >= ($this->regraContribuicao * 365)) {
            $this->analiseContribuicao = "OK";
        } else {
            $this->analiseContribuicao = "Ainda faltam<br/>{$resta1} dias.<hr id='geral' />Somente em {$this->dataCriterioTempoContribuicao}.";
        }

        /*
         *  Serviço Público Initerrupto
         */
        $resta2 = ($this->servicoPublico * 365) - $this->servidorTempoPublicoIninterrupto;
        $this->dataCriterioTempoServicoPublico = addDias($hoje, $resta2, false);  // retiro a contagem do primeiro dia para não contar hoje 2 vezes
        if ($this->servidorTempoPublicoIninterrupto >= ($this->servicoPublico * 365)) {
            $this->analisePublico = "OK";
        } else {
            $this->analisePublico = "Ainda faltam<br/>{$resta2} dias.<hr id='geral' />Somente em {$this->dataCriterioTempoServicoPublico}.";
        }

        /*
         *  Cargo Efetivo
         */
        $resta3 = ($this->cargoEfetivo * 365) - $this->servidorTempoUenf;
        $this->dataCriterioTempoCargo = addDias($hoje, $resta3, false);  // retiro a contagem do primeiro dia para não contar hoje 2 vezes
        if ($this->servidorTempoUenf >= ($this->cargoEfetivo * 365)) {
            $this->analiseCargoEfetivo = "OK";
        } else {
            $this->analiseCargoEfetivo = "Ainda faltam<br/>{$resta3} dias.<hr id='geral' />Somente em {$this->dataCriterioTempoCargo}.";
        }

        /*
         *  Pedágio
         */
        $this->servidorTempoAntes31_12_2021 = $aposentadoria->get_tempoTotalAntes31_12_21($this->idServidor);
        $this->servidorTempoSobra = ($this->regraContribuicao * 365) - $this->servidorTempoAntes31_12_2021;
        $this->servidorPedagio = round($this->servidorTempoSobra * ($this->pedagio / 100));

        if ($this->servidorPedagio < 0) {
            $this->dataCriterioPedagio = "---";
            $this->analisePedagio = "OK";
        } else {
            $this->dataCriterioPedagio = addDias($this->dataCriterioTempoContribuicao, $this->servidorPedagio, false);  // retiro a contagem do primeiro dia para não contar hoje 2 vezes

            if (jaPassou($this->dataCriterioPedagio)) {
                $this->analisePedagio = "OK";
            } else {
                $resta4 = getNumDias($hoje, $this->dataCriterioPedagio);
                $this->analisePedagio = "Ainda faltam<br/>{$resta4} dias.<hr id='geral' />Somente em {$this->dataCriterioPedagio}.";
            }
        }

        /*
         * Redutor
         */

        # Verifica se a data do critério idade é maior que o critério tempo (para ver se vale a pena a redução)
        if (dataMaior($this->dataCriterioTempoContribuicao, $this->dataCriterioIdade) == $this->dataCriterioIdade) {

            # Verifica o tempo de contribuição excedente até hoje
            $this->tempoExcedente = dataDif($this->dataCriterioTempoContribuicao, date("d/m/Y"));

            if ($this->tempoExcedente < 0) {
                $this->analiseReducao = "Não tem tempo de contribuição excedente.";
                $this->temDireito = false;
            }

            # Verifica o tempo que falta da idade na data em que alcança o tempo de contribuição
            $this->diasIdadeQueFalta = dataDif($this->dataCriterioTempoContribuicao, $this->dataCriterioIdade);
            $this->mesesIdadeQueFalta = ceil($this->diasIdadeQueFalta / 30);
            $this->diasParaPagar = ceil($this->diasIdadeQueFalta / 2);
            $this->mesesParaPagar = ceil($this->diasParaPagar / 30);

            # Data em que paga todos os dias que faltam para a idade
            $this->dataCriterioRedutor = addMeses($this->dataCriterioIdade, -$this->mesesParaPagar);

            # Muda a análise do critério idade
            $this->mensagemRedutor = "<br/><hr/ id='hrPrevisaoAposentAnalise'><p id='pLinha2'>Com Redutor</p>" . $this->dataCriterioRedutor;

            if (jaPassou($this->dataCriterioRedutor)) {
                $this->analiseIdade = "OK";
            }
        } else {
            $this->analiseReducao = "Não cabe o uso do redutor pois o servidor cumpriu o requisito de idade antes do de tempo de contribuição.";
        }

        ################
        # Data do Direito a Aposentadoria

        if (!empty($this->dataCriterioRedutor) AND dataMaior($this->dataCriterioRedutor, $this->dataCriterioIdade) == $this->dataCriterioIdade) {
            $this->dataDireitoAposentadoria = dataMaiorArray([
                $this->dataCriterioRedutor,
                $this->dataCriterioTempoContribuicao,
                $this->dataCriterioTempoServicoPublico,
                $this->dataCriterioTempoCargo,
                $this->dataCriterioPedagio
            ]);
        } else {
            $this->dataDireitoAposentadoria = dataMaiorArray([
                $this->dataCriterioIdade,
                $this->dataCriterioTempoContribuicao,
                $this->dataCriterioTempoServicoPublico,
                $this->dataCriterioTempoCargo,
                $this->dataCriterioPedagio
            ]);
        }

        # Define o texto de retorno 
        if (jaPassou($this->dataDireitoAposentadoria)) {
            $this->textoRetorno = "O Servidor tem direito a esta modalidade de aposentadoria desde:<br/><b>{$this->dataDireitoAposentadoria}</b>.";
            $this->textoReduzido = "Desde:<br/><b>{$this->dataDireitoAposentadoria}</b>";
            $this->corFundo = "success";
        } else {
            $this->textoRetorno = "O Servidor terá direito a esta modalidade de aposentadoria em:<br/><b>{$this->dataDireitoAposentadoria}</b>.";
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
                "{$this->servidorIdade} anos",
                $this->dataCriterioIdade . $this->mensagemRedutor,
                $this->analiseIdade],
            ["Contribuição",
                $this->tempoContribuiçãoDescricao,
                "{$this->regraContribuicao} anos<br/>(" . ($this->regraContribuicao * 365) . " dias)",
                intval($this->servidorTempoTotal / 365) . " anos<br/>{$this->servidorTempoTotal} dias",
                $this->dataCriterioTempoContribuicao,
                $this->analiseContribuicao],
            ["Pedágio",
                $this->pedagioDescricao,
                "{$this->pedagio} %",
                "{$this->servidorPedagio} dias",
                $this->dataCriterioPedagio,
                $this->analisePedagio],
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
                $this->analiseCargoEfetivo],
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
            ["<p id='pLinha1'>Pedágio</p><p id='pLinha4'>{$this->pedagioDescricao}</p>", $this->pedagio . " %", $this->pedagio . " %"],
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

        $figura = new Imagem(PASTA_FIGURAS . "transicaoPedagio3{$numero}.png", null, "100%", "100%");
        $figura->set_id('imgCasa');
        $figura->set_class('imagem');
        $figura->show();
    }

    ###########################################################

    public function exibeTempoAntes31_12_21($relatorio = false) {

        $aposentadoria = new Aposentadoria();
        $averbacao = new Averbacao();

        $array = [
            ["Cargo Efetivo - Uenf", $aposentadoria->get_tempoServicoUenfAntes31_12_21($this->idServidor)],
            ["Tempo Averbado", $averbacao->getTempoAverbadoAntes31_12_21($this->idServidor)]
        ];

        # Tabela Tempo até 31/12/2021
        if ($relatorio) {
            tituloRelatorio("Tempo até 31/12/2021");
            $tabela = new Relatorio();
            $tabela->set_cabecalhoRelatorio(false);
            $tabela->set_menuRelatorio(false);
            $tabela->set_totalRegistro(false);
            $tabela->set_dataImpressao(false);
            $tabela->set_bordaInterna(true);
            $tabela->set_log(false);
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo("Tempo até 31/12/2021");
        }

        $tabela->set_conteudo($array);
        $tabela->set_label(["Descrição", "Dias"]);
        $tabela->set_width([60, 40]);
        $tabela->set_align(["left", "center"]);
        $tabela->set_totalRegistro(false);
        $tabela->set_colunaSomatorio(1);
        $tabela->show();
    }

    ###########################################################

    public function exibeCalculoRedutor($relatorio = false) {

        $aposentadoria = new Aposentadoria();
        $averbacao = new Averbacao();

        if (dataMaior($this->dataCriterioTempoContribuicao, $this->dataCriterioIdade) == $this->dataCriterioIdade) {
            $array = [
                ["Tempo de Contribuição<br/>Excedente (em " . date("d/m/Y") . ")", $this->tempoExcedente . " dias<br/>(" . round($this->tempoExcedente / 30) . " meses)"],
                ["Tempo que Faltava para o<br/>Critério da Idade (em $this->dataCriterioTempoContribuicao)", $this->diasIdadeQueFalta . " dias<br/>(" . $this->mesesIdadeQueFalta . " meses)"],
                ["Tempo que leva para o tempo excedente pagar a idade", $this->diasParaPagar . " dias<br/>(" . $this->mesesParaPagar . " meses)"],
                ["Nova data do critário idade com o redutor", $this->dataCriterioRedutor]
            ];

            # Tabela Tempo até 31/12/2021
            if ($relatorio) {
                tituloRelatorio("Calculo do Redutor");
                $tabela = new Relatorio();
                $tabela->set_cabecalhoRelatorio(false);
                $tabela->set_menuRelatorio(false);
                $tabela->set_totalRegistro(false);
                $tabela->set_dataImpressao(false);
                $tabela->set_bordaInterna(true);
                $tabela->set_log(false);
            } else {
                $tabela = new Tabela();
                $tabela->set_titulo("Calculo do Redutor");
            }

            $tabela->set_conteudo($array);
            $tabela->set_label(["Descrição", "Valor"]);
            $tabela->set_width([60, 40]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_totalRegistro(false);
            #$tabela->set_colunaSomatorio(1);
            $tabela->show();
        } else {
            if ($relatorio) {
                tituloRelatorio("Calculo do Redutor");
            } else {
                tituloTable("Calculo do Redutor");
            }
            $painel = new Callout();
            $painel->abre();
            p($this->analiseReducao, "center");
            $painel->fecha();
        }
    }

    ###########################################################

    public function exibeCalculoPedagio($relatorio = false) {

        $array = [
            ["Contribuição até 31/12/2021", "{$this->servidorTempoAntes31_12_2021} dias"],
            ["Regra da Aposentadoria", ($this->regraContribuicao * 365) . " dias<br/>({$this->regraContribuicao} anos)"],
            ["Tempo que Faltava em 01/01/2022", "{$this->servidorTempoSobra} dias"],
            ["Pedágio (20%)", $this->servidorPedagio . " dias"]
        ];

        # Cálculo do Pedágio
        if ($relatorio) {
            tituloRelatorio("Cálculo do Pedágio");
            $tabela = new Relatorio();
            $tabela->set_cabecalhoRelatorio(false);
            $tabela->set_menuRelatorio(false);
            $tabela->set_totalRegistro(false);
            $tabela->set_dataImpressao(false);
            $tabela->set_bordaInterna(true);
            $tabela->set_log(false);
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo("Cálculo do Pedágio");
        }

        $tabela->set_conteudo($array);
        $tabela->set_label(["Descrição", "Valor"]);
        $tabela->set_width([60, 40]);
        $tabela->set_align(["left", "center"]);
        $tabela->set_totalRegistro(false);
        $tabela->show();
    }

    ###########################################################

    public function exibeCalculoRedutorDetalhado($relatorio = false) {

        # Verifica se cabe o redutor
        if (dataMaior($this->dataCriterioTempoContribuicao, $this->dataCriterioIdade) == $this->dataCriterioIdade) {

            # Define os anos
            $anoInicial = year($this->dataCriterioTempoContribuicao);
            $mesInicial = month($this->dataCriterioTempoContribuicao);
            $anoFinal = year($this->dataCriterioRedutor) + 1;
            $mesFinal = month($this->dataCriterioRedutor);

            $mesesPagos = 0;
            $mesesParaPagar = round($this->diasIdadeQueFalta / 30);
            $dataIdade = $this->dataCriterioIdade;
            $analiseRedutor = null;
            $contador = 1;

            # Caminha com os anos
            for ($i = $anoInicial; $i <= $anoFinal; $i++) {

                # ajeita quando é o primeiro ano
                if ($i == $anoInicial) {
                    $m1 = $mesInicial;
                } else {
                    $m1 = 1;
                }

                # Caminha com os meses
                for ($m = $m1; $m <= 12; $m++) {

                    if ($mesesPagos == 0) {
                        $analiseRedutor = "Não há excedente no<br/>tempo de Contribuição.";
                    } elseif ($analiseRedutor <> "OK") {
                        $analiseRedutor = "---";
                    }

                    $dataIdade = addMeses($dataIdade, -1);

                    # Verifica se chegou
                    if ($this->mesesParaPagar == $contador) {
                        $analiseRedutor = "OK";
                    }

                    $array[] = [$contador, $i, $m, $mesesParaPagar, $mesesPagos, $mesesParaPagar - $mesesPagos, $dataIdade, $analiseRedutor];
                    $mesesPagos++;
                    $mesesPagos++;

                    if ($analiseRedutor == "OK") {
                        break;
                    }

                    $contador++;
                }

                if ($analiseRedutor == "OK") {
                    break;
                }
            }

            # Exibe a tabela
            if ($relatorio) {
                tituloRelatorio("Histórico da Redução");
                $tabela = new Relatorio();
                $tabela->set_cabecalhoRelatorio(false);
                $tabela->set_menuRelatorio(false);
                $tabela->set_totalRegistro(false);
                $tabela->set_dataImpressao(false);
                $tabela->set_bordaInterna(true);
                $tabela->set_log(false);
            } else {
                $tabela = new Tabela();
                $tabela->set_titulo("Histórico da Redução");
                $tabela->set_subtitulo("(A cada mês o servidor paga 2 meses da idade)");
            }

            $tabela->set_conteudo($array);
            $tabela->set_label(["#", "Ano", "Mês", "Para Pagar<br/>(em meses)", "Pagos<br/>(em meses)", "Faltam<br/>(em meses)", "Data da Idade", "Análise"]);
            $tabela->set_funcao([null, null, "get_nomeMes"]);
            $tabela->set_width([4, 6, 14, 14, 14, 14, 14, 20]);
            $tabela->set_rowspan(1);
            $tabela->set_grupoCorColuna(1);
            $tabela->set_totalRegistro(false);

            if (!$relatorio) {
                $tabela->set_formatacaoCondicional(array(
                    array('coluna' => 7,
                        'operador' => '=',
                        'valor' => "OK",
                        'id' => 'vigente')));
            }
            $tabela->show();
        }
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
        $link = "?fase=carregarPagina&id={$idServidor}&link=pedagioReducao";

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
