<?php

class AposentadoriaDireitoAdquirido3 {

    /**
     * Abriga as rotina referentes a aposentadoria do servidor
     * Direito adquirido
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    # Id Servidor
    private $idServidor = null;

    # Descricao
    private $tipo = "Direito Adquirido";
    private $descricao = "Aposentadoria por Idade e Tempo de Contribuição<br/>Regra do Pedágio - Média";
    private $legislacao = "Art. 2º da EC Nº 41/2003";

    # Regras
    private $idadeHomem = 53;
    private $idadeMulher = 48;
    private $dtIngresso = "16/12/1998";
    private $contribuicaoHomem = 35;
    private $contribuicaoMulher = 30;
    private $servicoPublico = 5;
    private $cargoEfetivo = 5;
    private $dtRequesitosCumpridos = "31/12/2021";

    # Remuneração
    private $calculoInicial = "Média aritmética simples dos 80% das maiores remunerações corrigidas desde julho/94 - Lei Federal 10.887<br/>
                               Redutor:<br/>
                                       - Até 31/12/2005 - Redutor de 3,5% x nº de anos (reduzidos em relação a idade – 60 H/ 55 M)<br/>
                                       - Após 01/01/2006 - Redutor de 5% x nº de anos (reduzidos em relação a idade – 60 H/55 M)";
    private $teto = "Remuneração do servidor no cargo efetivo";
    private $reajuste = "INPC – Aplicado em Janeiro – Lei 6.244/2012";
    private $paridade = "SEM PARIDADE";

    # Descrições
    private $dtIngressoDescricao = "Data de ingresso no serviço público sem interrupção.";
    private $tempoContribuiçãoDescricao = "Tempo Total averbado<br/>(público e privado).";
    private $idadeDescricao = "Idade do servidor.";
    private $tempoPublicoDescicao = "Tempo de todos os periodo públicos ininterruptos.";
    private $tempoCargoDescicao = "Tempo no mesmo órgão e mesmo cargo.";
    private $dtRequesitosCumpridosDescicao = "Data limite para o cumprimento dos requesito.";

    # Dados do servidor
    public $analisaDtIngresso = null;
    public $analiseIdade = null;
    public $analiseContribuicao = null;
    public $analisePublico = null;
    public $analiseCargoEfetivo = null;
    public $analiseDtRequesitosCumpridos = null;

    # Variaveis de Retorno    
    public $dataCriterioIngresso = null;
    public $dataCriterioIdade = null;
    public $dataCriterioTempoContribuicao = null;
    public $dataCriterioTempoServicoPublico = null;
    public $dataCriterioTempoCargo = null;
    public $dataDireitoAposentadoria = null;
    public $temDireito = true;

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
        $dtNasc = $pessoal->get_dataNascimento($this->idServidor);
        $idadeServidor = $pessoal->get_idade($this->idServidor);
        $sexo = $pessoal->get_sexo($this->idServidor);

        $averbacao = new Averbacao();
        $tempoAverbadoPublico = $averbacao->get_tempoAverbadoPublico($this->idServidor);
        $tempoAverbadoPrivado = $averbacao->get_tempoAverbadoPrivado($this->idServidor);

        $aposentadoria = new Aposentadoria();
        $tempoUenf = $aposentadoria->get_tempoServicoUenf($this->idServidor);
        $dtIngressosServidor = $aposentadoria->get_dtIngresso($this->idServidor);

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

        # Data de Ingresso        
        if (dataMaior($this->dtIngresso, $dtIngressosServidor) == $this->dtIngresso) {
            $this->analisaDtIngresso = "OK";
        } else {
            $this->analisaDtIngresso = "Não Tem Direito";
        }

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
        $this->dataCriterioTempoContribuicao = addDias($hoje, $resta1, false);  // retiro a contagem do primeiro dia para não contar hoje 2 vezes
        if ($tempoTotal >= ($regraContribuicao * 365)) {
            $this->analiseContribuicao = "OK";
        } else {
            $this->analiseContribuicao = "Ainda faltam<br/>{$resta1} dias.<hr id='geral' />Somente em<br/>{$this->dataCriterioTempoContribuicao}.";
        }

        # Serviço Público Initerrupto
        $resta2 = ($this->servicoPublico * 365) - $tempoPublicoIninterrupto;
        $this->dataCriterioTempoServicoPublico = addDias($hoje, $resta2, false);  // retiro a contagem do primeiro dia para não contar hoje 2 vezes
        if ($tempoPublicoIninterrupto >= ($this->servicoPublico * 365)) {
            $this->analisePublico = "OK";
        } else {
            $this->analisePublico = "Ainda faltam<br/>{$resta2} dias.<hr id='geral' />Somentee em<br/>{$this->dataCriterioTempoServicoPublico}.";
        }

        # Cargo Efetivo
        $resta3 = ($this->cargoEfetivo * 365) - $tempoUenf;
        $this->dataCriterioTempoCargo = addDias($hoje, $resta3, false);  // retiro a contagem do primeiro dia para não contar hoje 2 vezes
        if ($tempoUenf >= ($this->cargoEfetivo * 365)) {
            $this->analiseCargoEfetivo = "OK";
        } else {
            $this->analiseCargoEfetivo = "Ainda faltam<br/>{$resta3} dias.<hr id='geral' />Somente em<br/>{$this->dataCriterioTempoCargo}.";
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
            $this->analiseDtRequesitosCumpridos = "Não Tem Direito";
            $this->temDireito = false;
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
        $dtIngressosServidor = $aposentadoria->get_dtIngresso($this->idServidor);

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
            ["Data de Ingresso", $this->dtIngressoDescricao, $this->dtIngresso, $dtIngressosServidor, "---", $this->analisaDtIngresso],
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
        $tabela->set_width([14, 30, 14, 14, 14, 14]);
        $tabela->set_align(["left", "left"]);
        $tabela->set_totalRegistro(false);
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
        $tabela->show();
    }

    ###########################################################

    public function exibeAnaliseResumo() {

        # Verifica a data limite
        if ($this->analiseDtRequesitosCumpridos == "OK") {
            if (jaPassou($this->dataDireitoAposentadoria)) {
                $texto = "O Servidor tem direito a esta modalidade de aposentadoria desde:<br/><b>{$this->dataDireitoAposentadoria}</b>";
                $cor = "success";
            } else {
                $texto = "O Servidor terá direito a esta modalidade de aposentadoria em:<br/><b>{$this->dataDireitoAposentadoria}</b>";
                $cor = "secondary";
            }
        } else {
            $texto = "O Servidor <b>Não Tem Direito</b><br/>a essa modalidade de aposentadoria.";
            $cor = "warning";
        }

        # Verifica a regra extra da data de ingresso
        if ($this->analisaDtIngresso == "Não Tem Direito") {
            $texto = "O Servidor <b>Não Tem Direito</b><br/>a essa modalidade de aposentadoria.";
            $cor = "alert";
        }

        # Exibe o resumo
        $painel = new Callout($cor);
        $painel->abre();

        p($texto, "center");

        $painel->fecha();
    }

    ###########################################################

    public function getDataAposentadoria($idServidor) {

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

    public function getDiasFaltantes($idServidor) {

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
    
    public function exibeRegras() {

        $array = [
            ["<p id='pLinha1'>Data de Ingresso</p><p id='pLinha4'>{$this->dtIngressoDescricao}</p>", $this->dtIngresso, $this->dtIngresso],
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

    public function get_tipo() {

        return $this->tipo;
    }

    ###########################################################

    public function get_legislacao() {

        return $this->legislacao;
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
