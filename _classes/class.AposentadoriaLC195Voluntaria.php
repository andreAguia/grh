<?php

class AposentadoriaLC195Voluntaria {

    /**
     * Abriga as rotina referentes a aposentadoria do servidor
     * Direito adquirido
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    # Id Servidor
    private $idServidor = null;

    # Descrição
    private $descricao = "Aposentadoria Voluntária por Idade e Tempo de Contribuição<br/>Art. 2º, inciso III, da Lei Complementar nº 195/2021";

    # Texto
    private $texto = "A regra permanente é aplicável a todos os servidores, independentemente da data do
ingresso no serviço público. Para se aposentar segundo os critérios das regras permanentes, os
servidores deverão cumprir os requisitos de cada uma das espécies de aposentadoria, confor-
me enumerado, havendo possibilidade de concessão de aposentadorias especiais, como por
exemplo, os professores.
ATENÇÃO PARA A MUDANÇA: A regra permanente, que antes era extraída do artigo
40, § 1º, inciso III, alínea “a”, da Constituição Federal, com redação dada pela EC nº 20/1998,
mudou com a publicação da nova LC Estadual nº 195, de 05 de outubro de 2021. A nova regra
permanente, que será detalhada a seguir, é de aplicação obrigatória para todos os servidores
que ingressarem no serviço público estadual após a entrada em vigor da LC Estadual
nº 195, isto é, após 01/01/2022, ou a qualquer servidor que já estava no serviço público estadual antes de sua entrada em vigor e venha a optar por suas regras. Então, podemos resumir
a obrigatoriedade da nova regra permanente contida na LC Estadual nº 195/2021 da seguinte
maneira:
• Aplicável obrigatoriamente aos servidores que ingressarem no Estado a
partir de 01/01/2022, ou a qualquer servidor que opte por esta regra.";

    /*
     * Regras
     */
    # Data de Ingresso
    private $dtIngresso = null;

    # Data Limite do direito
    private $dtRequesitosCumpridos = null;
    private $dtRequesitosCumpridosDescicao = "Data limite para o cumprimento dos requesito.";

    # Idade
    private $idadeHomem = 65;
    private $idadeMulher = 62;
    private $idadeDescricao = "Idade do servidor.";

    # Tempo de Contrivuição
    private $contribuicaoHomem = 25;
    private $contribuicaoMulher = 25;
    private $tempoContribuiçãoDescricao = "Tempo Total averbado (público e privado).";

    # Tempo de Serviço Público
    private $servicoPublico = 10;
    private $tempoPublicoDescicao = "Tempo de todos os periodo públicos ininterruptos.";

    # Tempo no cargo efetivo
    private $cargoEfetivo = 5;
    private $tempoCargoDescicao = "Tempo no mesmo órgão e mesmo cargo.";

    # Remuneração
    private $calculoInicial = "Média aritmética simples de TODAS as remunerações a partir de julho de 1994 - Lei Federal 10.887";
    private $percentualDevido = "60% + 2% para cada ano que exceder 20 anos de contribuição";
    private $reajuste = "INPC – Lei 6.244/2012";
    private $paridade = "SEM PARIDADE";

    ###########################################################    
    # Dados do servidor
    public $analiseIdade = null;
    public $analiseContribuicao = null;
    public $analisePublico = null;
    public $analiseCargoEfetivo = null;
    public $analiseDtRequesitosCumpridos = null;

    # Variaveis de Retorno
    public $dataCriterioIdade = null;
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
        $dtNasc = $pessoal->get_dataNascimento($this->idServidor);
        $idadeServidor = $pessoal->get_idade($this->idServidor);
        $sexo = $pessoal->get_sexo($this->idServidor);

        $averbacao = new Averbacao();
        $tempoAverbadoPublico = $averbacao->get_tempoAverbadoPublico($this->idServidor);
        $tempoAverbadoPrivado = $averbacao->get_tempoAverbadoPrivado($this->idServidor);

        $aposentadoria = new Aposentadoria();
        $tempoUenf = $aposentadoria->get_tempoServicoUenf($this->idServidor);

        $tempoTotal = $tempoAverbadoPublico + $tempoAverbadoPrivado + $tempoUenf;
        $tempoPublicoIninterrupto = $aposentadoria->get_tempoPublicoIninterrupto($this->idServidor);

        if ($sexo == "Masculino") {
            $regraIdade = $this->idadeHomem;
            $regraContribuicao = $this->contribuicaoHomem;
        } else {
            $regraIdade = $this->idadeMulher;
            $regraContribuicao = $this->contribuicaoMulher;
        }

        $hoje = date("d/m/Y");

        /*
         * Análise
         */

        # Idade
        $this->dataCriterioIdade = addAnos($dtNasc, $regraIdade);
        if ($idadeServidor >= $regraIdade) {
            $this->analiseIdade = "OK";
        } else {
            # Calcula a data
            $this->analiseIdade = "Somente em {$this->dataCriterioIdade}.";
        }

        # Tempo de Contribuição
        $resta1 = ($regraContribuicao * 365) - $tempoTotal;
        $this->dataCriterioTempoContribuicao = addDias($hoje, $resta1);
        if ($tempoTotal >= ($regraContribuicao * 365)) {
            $this->analiseContribuicao = "OK";
        } else {
            $this->analiseContribuicao = "Ainda faltam {$resta1} dias<br/>Somente em {$this->dataCriterioTempoContribuicao}.";
        }

        # Serviço Público Initerrupto
        $resta2 = ($this->servicoPublico * 365) - $tempoPublicoIninterrupto;
        $this->dataCriterioTempoServicoPublico = addDias($hoje, $resta2);
        if ($tempoPublicoIninterrupto >= ($this->servicoPublico * 365)) {
            $this->analisePublico = "OK";
        } else {
            $this->analisePublico = "Ainda faltam {$resta2} dias<br/>Somente em {$this->dataCriterioTempoServicoPublico}.";
        }

        # Cargo Efetivo
        $resta3 = ($this->cargoEfetivo * 365) - $tempoUenf;
        $this->dataCriterioTempoCargo = addDias($hoje, $resta3);
        if ($tempoUenf >= ($this->cargoEfetivo * 365)) {
            $this->analiseCargoEfetivo = "OK";
        } else {
            $this->analiseCargoEfetivo = "Ainda faltam {$resta3} dias<br/>Somente em {$this->dataCriterioTempoCargo}.";
        }

        # Data do Direito a Aposentadoria
        $this->dataDireitoAposentadoria = dataMaiorArray([
            $this->dataCriterioIdade,
            $this->dataCriterioTempoContribuicao,
            $this->dataCriterioTempoServicoPublico,
            $this->dataCriterioTempoCargo
        ]);
    }

    ###########################################################

    public function exibeAnalise() {

        # Pega os dados do servidor
        $pessoal = new Pessoal();
        $idadeServidor = $pessoal->get_idade($this->idServidor);
        $sexo = $pessoal->get_sexo($this->idServidor);

        $averbacao = new Averbacao();
        $tempoAverbadoPublico = $averbacao->get_tempoAverbadoPublico($this->idServidor);
        $tempoAverbadoPrivado = $averbacao->get_tempoAverbadoPrivado($this->idServidor);

        $aposentadoria = new Aposentadoria();
        $tempoUenf = $aposentadoria->get_tempoServicoUenf($this->idServidor);

        $tempoTotal = $tempoAverbadoPublico + $tempoAverbadoPrivado + $tempoUenf;
        $tempoPublicoIninterrupto = $aposentadoria->get_tempoPublicoIninterrupto($this->idServidor);

        if ($sexo == "Masculino") {
            $regraIdade = $this->idadeHomem;
            $regraContribuicao = $this->contribuicaoHomem;
        } else {
            $regraIdade = $this->idadeMulher;
            $regraContribuicao = $this->contribuicaoMulher;
        }

        /*
         *  Tabela
         */

        $array = [
            ["Idade", $this->idadeDescricao, "{$regraIdade} anos", "{$idadeServidor} anos", $this->dataCriterioIdade, $this->analiseIdade],
            ["Contribuição", $this->tempoContribuiçãoDescricao, "{$regraContribuicao} anos<br/>(" . ($regraContribuicao * 365) . " dias)", "{$tempoTotal} dias", $this->dataCriterioTempoContribuicao, $this->analiseContribuicao],
            ["Serviço Público", $this->tempoPublicoDescicao, "{$this->servicoPublico} anos<br/>(" . ($this->servicoPublico * 365) . " dias)", "{$tempoPublicoIninterrupto} dias", $this->dataCriterioTempoServicoPublico, $this->analisePublico],
            ["Cargo Efetivo", $this->tempoCargoDescicao, "{$this->cargoEfetivo} anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)", "{$tempoUenf} dias", $this->dataCriterioTempoCargo, $this->analiseCargoEfetivo],
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
        $tabela->show();
    }

    ###########################################################

    public function exibeAnaliseResumo() {

        # Verifica a data limite        
        if ($this->analiseDtRequesitosCumpridos == "OK" OR $this->dtRequesitosCumpridos == null) {
            if (jaPassou($this->dataDireitoAposentadoria)) {
                $texto = "O Servidor tem direito a esta modalidade de aposentadoria desde:<br/><b>{$this->dataDireitoAposentadoria}</b>";
                $cor = "success";
            } else {
                $texto = "O Servidor terá direito a esta modalidade de aposentadoria em:<br/><b>{$this->dataDireitoAposentadoria}</b>";
                $cor = "warning";
            }
        } else {
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
            ["<p id='pLinha1'>Idade</p><p id='pLinha4'>{$this->idadeDescricao}</p>", $this->idadeMulher . " anos", $this->idadeHomem . " anos"],
            ["<p id='pLinha1'>Contribuição</p><p id='pLinha4'>{$this->tempoContribuiçãoDescricao}</p>", $this->contribuicaoMulher . " anos<br/>(" . ($this->contribuicaoMulher * 365) . " dias)", $this->contribuicaoHomem . " anos<br/>(" . ($this->contribuicaoHomem * 365) . " dias)"],
            ["<p id='pLinha1'>Serviço Público</p><p id='pLinha4'>{$this->tempoPublicoDescicao}</p>", $this->servicoPublico . " anos<br/>(" . ($this->servicoPublico * 365) . " dias)", $this->servicoPublico . " anos<br/>(" . ($this->servicoPublico * 365) . " dias)"],
            ["<p id='pLinha1'>Cargo Efetivo</p><p id='pLinha4'>{$this->tempoCargoDescicao}</p>", $this->cargoEfetivo . " anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)", $this->cargoEfetivo . " anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)"],];

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
            ["Percentual Devido", $this->percentualDevido],
            ["Reajuste", $this->reajuste],
            ["Paridade", $this->paridade]
        ];

        $tabela = new Tabela();
        $tabela->set_titulo("Remuneração");
        $tabela->set_conteudo($array);
        $tabela->set_label(array("Item", "Descrição"));
        $tabela->set_width(array(30, 70));
        $tabela->set_align(array("left", "left"));
        $tabela->set_totalRegistro(false);
        $tabela->show();
    }

    ###########################################################

    public function exibeResumoCartilha($numero = 1) {

        $figura = new Imagem(PASTA_FIGURAS . "lc195voluntaria{$numero}.png", null, "100%", "100%");
        $figura->set_id('imgCasa');
        $figura->set_class('imagem');
        $figura->show();
    }

    ###########################################################

    public function get_descricao() {

        return $this->descricao;
    }

    ###########################################################

    public function exibeDescricaoLink($link = null) {

        $link = new Link($this->descricao, $link);
        $link->show();
    }

    ###########################################################
}
