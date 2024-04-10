<?php

class AposentadoriaTransicaoPedagio1 {

    /**
     * Aposentadoria Regras de Transição Pedagio 1
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    # Id Servidor
    private $idServidor = null;

    # Descrição
    private $descricao = "Regra do Pedágio<br/>Por Idade e Tempo de Contribuição<br/>Integralidade e Paridade - inciso V do art. 4º da EC nº 90/2021";

    # Regras
    private $idadeHomem = 60;
    private $idadeMulher = 55;
    private $dtIngresso = "31/12/2003";
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
    public $servidorDataNascimento = null;
    public $servidorIdade = null;
    public $servidorSexo = null;
    public $serviçoTempoTotal = null;
    public $servidorDataIngresso = null;
    public $servidorPontos = null;
    public $servidorTempoAntes31_12_2021 = null;
    public $servidorTempoSobra = null;
    public $servidorPedagio = null;

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
    public $analisePedagio = null;

    # Variaveis de Retorno    
    public $dataCriterioIngresso = null;
    public $dataCriterioIdade = null;
    public $dataCriterioPedagio = null;
    public $dataCriterioTempoContribuicao = null;
    public $dataCriterioTempoServicoPublico = null;
    public $dataCriterioTempoCargo = null;
    public $dataDireitoAposentadoria = null;

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
            $this->analiseContribuicao = "Ainda faltam<br/>{$resta1} dias.<hr id='geral' />Somente em {$this->dataCriterioTempoContribuicao}.";
        }

        # Serviço Público Initerrupto
        $resta2 = ($this->servicoPublico * 365) - $this->servidorTempoPublicoIninterrupto;
        $this->dataCriterioTempoServicoPublico = addDias($hoje, $resta2);
        if ($this->servidorTempoPublicoIninterrupto >= ($this->servicoPublico * 365)) {
            $this->analisePublico = "OK";
        } else {
            $this->analisePublico = "Ainda faltam<br/>{$resta2} dias.<hr id='geral' />Somente em {$this->dataCriterioTempoServicoPublico}.";
        }

        # Cargo Efetivo
        $resta3 = ($this->cargoEfetivo * 365) - $this->servidorTempoUenf;
        $this->dataCriterioTempoCargo = addDias($hoje, $resta3);
        if ($this->servidorTempoUenf >= ($this->cargoEfetivo * 365)) {
            $this->analiseCargoEfetivo = "OK";
        } else {
            $this->analiseCargoEfetivo = "Ainda faltam<br/>{$resta3} dias.<hr id='geral' />Somente em {$this->dataCriterioTempoCargo}.";
        }

        # Pedágio
        $this->servidorTempoAntes31_12_2021 = $aposentadoria->get_tempoTotalAntes31_12_21($this->idServidor);
        $this->servidorTempoSobra = ($this->regraContribuicao * 365) - $this->servidorTempoAntes31_12_2021;
        $this->servidorPedagio = intval($this->servidorTempoSobra * ($this->pedagio / 100));

        if ($this->servidorPedagio < 0) {
            $this->dataCriterioPedagio = "---";
            $this->analisePedagio = "OK";
        } else {
            $this->dataCriterioPedagio = addDias($this->dataCriterioTempoContribuicao, $this->servidorPedagio);

            if (jaPassou($this->dataCriterioPedagio)) {
                $this->analisePedagio = "OK";
            } else {
                $resta4 = getNumDias($hoje, $this->dataCriterioPedagio);
                $this->analisePedagio = "Ainda faltam<br/>{$resta4} dias.<hr id='geral' />Somente em {$this->dataCriterioPedagio}.";
            }
        }

        # Data do Direito a Aposentadoria
        $this->dataDireitoAposentadoria = dataMaiorArray([
            $this->dataCriterioIdade,
            $this->dataCriterioTempoContribuicao,
            $this->dataCriterioTempoServicoPublico,
            $this->dataCriterioTempoCargo,
            $this->dataCriterioPedagio
        ]);
    }

    ###########################################################

    public function exibeAnalise() {

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
            ["Pedágio", $this->pedagioDescricao, "{$this->pedagio} %", "{$this->servidorPedagio} dias", $this->dataCriterioPedagio, $this->analisePedagio],
            ["Serviço Público", $this->tempoPublicoDescicao, "{$this->servicoPublico} anos<br/>(" . ($this->servicoPublico * 365) . " dias)", "{$this->servidorTempoPublicoIninterrupto} dias", $this->dataCriterioTempoServicoPublico, $this->analisePublico],
            ["Cargo Efetivo", $this->tempoCargoDescicao, "{$this->cargoEfetivo} anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)", "{$this->servidorTempoUenf} dias", $this->dataCriterioTempoCargo, $this->analiseCargoEfetivo]
        ];

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_titulo("Dados");
        $tabela->set_conteudo($array);
        $tabela->set_label(["Item", "Descrição", "Regra", "Servidor", "Data", "Análise"]);
        $tabela->set_width([14, 30, 14, 14, 14, 14]);
        $tabela->set_align(["left", "left"]);
        $tabela->set_totalRegistro(false);
        $tabela->set_formatacaoCondicional(array(
            array('coluna' => 5,
                'valor' => 'OK',
                'operador' => '=',
                'id' => 'emAberto'),
            array('coluna' => 5,
                'valor' => "NÃO TEM DIREITO",
                'operador' => '=',
                'id' => 'indeferido'),
            array('coluna' => 5,
                'valor' => 'OK',
                'operador' => '<>',
                'id' => 'arquivado')
        ));
        $tabela->show();

        # Mensagem
        if (!empty($mensagem)) {
            callout($mensagem);
        }
    }

    ###########################################################

    public function exibeAnaliseResumo() {

        # Verifica a data limite
        if (jaPassou($this->dataDireitoAposentadoria)) {
            $texto = "O Servidor tem direito a esta modalidade de aposentadoria desde:<br/><b>{$this->dataDireitoAposentadoria}</b>.";
            $cor = "success";
        } else {
            $texto = "O Servidor terá direito a esta modalidade de aposentadoria em:<br/><b>{$this->dataDireitoAposentadoria}</b>.";
            $cor = "warning";
        }

        # Verifica a regra extra da data de ingresso
        if ($this->analisaDtIngresso == "NÃO TEM DIREITO") {
            $texto = "O Servidor <b>NÃO TEM DIREITO</b> a essa modalidade de aposentadoria.";
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
            ["<p id='pLinha1'>Pedágio</p><p id='pLinha4'>{$this->pedagioDescricao}</p>", $this->pedagio . " %", $this->pedagio . " %"],
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

        $figura = new Imagem(PASTA_FIGURAS . "transicaoPedagio1{$numero}.png", null, "100%", "100%");
        $figura->set_id('imgCasa');
        $figura->set_class('imagem');
        $figura->show();
    }

    ###########################################################

    public function get_descricao() {

        return $this->descricao;
    }

    ###########################################################

    public function exibeTempoAntes31_12_21() {

        $aposentadoria = new Aposentadoria();
        $averbacao = new Averbacao();

        $array = [
            ["Cargo Efetivo - Uenf", $aposentadoria->get_tempoServicoUenfAntes31_12_21($this->idServidor)],
            ["Tempo Averbado", $averbacao->getTempoAverbadoAntes31_12_21($this->idServidor)]
        ];

        # Tabela Tempo até 31/12/2021
        $tabela = new Tabela();
        $tabela->set_titulo("Tempo até 31/12/2021");
        #$tabela->set_subtitulo("Em Dias");
        $tabela->set_conteudo($array);
        $tabela->set_label(["Descrição", "Dias"]);
        $tabela->set_width([60, 40]);
        $tabela->set_align(["left", "center"]);
        $tabela->set_totalRegistro(false);
        $tabela->set_colunaSomatorio(1);
        $tabela->show();
    }

    ###########################################################

    public function exibeCalculoPedagio() {

        $array = [
            ["Contribuição até 31/12/2021", "{$this->servidorTempoAntes31_12_2021} dias"],
            ["Regra da Aposentadoria", ($this->regraContribuicao * 365) . " dias<br/>({$this->regraContribuicao} anos)"],
            ["Tempo que Faltava em 01/01/2022", "{$this->servidorTempoSobra} dias"],
            ["Pedágio (20%)", $this->servidorPedagio . " dias"]
        ];

        # Cálculo do Pedágio
        $tabela = new Tabela();
        $tabela->set_titulo("Cálculo do Pedágio");
        #$tabela->set_subtitulo("Em Dias");
        $tabela->set_conteudo($array);
        $tabela->set_label(["Descrição", "Valor"]);
        $tabela->set_width([60, 40]);
        $tabela->set_align(["left", "center"]);
        $tabela->set_totalRegistro(false);
        $tabela->show();
    }

    ###########################################################
}