<?php

class AposentadoriaDiretoAdquirido1 {

    /**
     * Abriga as rotina referentes a aposentadoria do servidor
     * Direito adquirido
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    # Id Servidor
    private $idServidor = null;

    # Descrição
    private $descricao = "Aposentadoria Voluntária por Idade e Tempo de Contribuição<br/>C.F. Art. 40, §1º, III, alínea a";

    # Regras
    private $dtIngresso = null;
    private $idadeHomem = 60;
    private $idadeMulher = 55;
    private $contribuicaoHomem = 35;
    private $contribuicaoMulher = 30;
    private $servicoPublico = 10;
    private $cargoEfetivo = 5;
    private $dtRequesitosCumpridos = "31/12/2021";

    # Remuneração
    private $calculoInicial = "Média aritmética simples dos 80% das maiores remunerações de contribuições corrigidas desde julho/94 - Lei Federal 10.887";
    private $teto = "Última remuneração do servidor no cargo efetivo";
    private $reajuste = "Os proventos deverão ser reajustados na mesma data e índice adotados para o reajuste dos benefícios do regime geral de previdência social";
    private $paridade = "SEM PARIDADE";

    # Descrições
    private $tempoContribuiçãoDescricao = "Tempo Total averbado (público e privado).";
    private $idadeDescricao = "Idade do servidor.";
    private $tempoPublicoDescicao = "Tempo de todos os periodo públicos ininterruptos.";
    private $tempoCargoDescicao = "Tempo no mesmo órgão e mesmo cargo.";
    private $dtRequesitosCumpridosDescicao = "Data limite para o cumprimento dos requesito.";

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

        # Data limite do cumprimento dos requisitos
        if (dataMaior($this->dtRequesitosCumpridos, $this->dataDireitoAposentadoria) == $this->dtRequesitosCumpridos) {
            $this->analiseDtRequesitosCumpridos = "OK";
        } else {
            $this->analiseDtRequesitosCumpridos = "NÃO TEM DIREITO";
        }
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
            ["Data Limite", $this->dtRequesitosCumpridosDescicao, $this->dtRequesitosCumpridos, $this->dataDireitoAposentadoria, "-", $this->analiseDtRequesitosCumpridos],
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
    }

    ###########################################################

    public function exibeAnaliseResumo() {

        # Verifica a data limite
        if ($this->analiseDtRequesitosCumpridos == "OK") {
            if (jaPassou($this->dataDireitoAposentadoria)) {
                $texto = "O Servidor tem direito a esta modalidade de aposentadoria desde: <b>{$this->dataDireitoAposentadoria}</b>.";
                $cor = "success";
            } else {
                $texto = "O Servidor terá direito a esta modalidade de aposentadoria em: <b>{$this->dataDireitoAposentadoria}</b>.";
                $cor = "secondary";
            }
        } else {
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
            ["<p id='pLinha1'>Idade</p><p id='pLinha4'>{$this->idadeDescricao}</p>", $this->idadeMulher . " anos", $this->idadeHomem . " anos"],
            ["<p id='pLinha1'>Contribuição</p><p id='pLinha4'>{$this->tempoContribuiçãoDescricao}</p>", $this->contribuicaoMulher . " anos<br/>(" . ($this->contribuicaoMulher * 365) . " dias)", $this->contribuicaoHomem . " anos<br/>(" . ($this->contribuicaoHomem * 365) . " dias)"],
            ["<p id='pLinha1'>Serviço Público</p><p id='pLinha4'>{$this->tempoPublicoDescicao}</p>", $this->servicoPublico . " anos<br/>(" . ($this->servicoPublico * 365) . " dias)", $this->servicoPublico . " anos<br/>(" . ($this->servicoPublico * 365) . " dias)"],
            ["<p id='pLinha1'>Cargo Efetivo</p><p id='pLinha4'>{$this->tempoCargoDescicao}</p>", $this->cargoEfetivo . " anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)", $this->cargoEfetivo . " anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)"],
            ["<p id='pLinha1'>Data Limite</p><p id='pLinha4'>{$this->dtRequesitosCumpridosDescicao}</p>", $this->dtRequesitosCumpridos, $this->dtRequesitosCumpridos],
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
        $tabela->set_label(array("Item", "Descrição"));
        $tabela->set_width(array(30, 70));
        $tabela->set_align(array("left", "left"));
        $tabela->set_totalRegistro(false);
        $tabela->show();
    }

    ###########################################################

    public function get_descricao() {

        return $this->descricao;
    }

    ###########################################################

    public function exibeRegraSite() {

        $figura = new Imagem(PASTA_FIGURAS . "aposentadoriaDireitoAdquirido1.jpg", null, "100%", "100%");
        $figura->set_id('imgCasa');
        $figura->set_class('imagem');
        $figura->show();
    }
    
    ###########################################################
}
