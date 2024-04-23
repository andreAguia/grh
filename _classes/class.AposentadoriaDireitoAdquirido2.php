<?php

class AposentadoriaDireitoAdquirido2 {

    /**
     * Abriga as rotina referentes a aposentadoria do servidor
     * Direito adquirido
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    # Id Servidor
    private $idServidor = null;

    # Descrição
    private $descricao = "Aposentadoria Voluntária por Idade<br/>C.F. Art. 40, §1º, III, alínea b";

    # Regras
    private $dtIngresso = null;
    private $idadeHomem = 65;
    private $idadeMulher = 60;
    private $servicoPublico = 10;
    private $cargoEfetivo = 5;
    private $dtRequesitosCumpridos = "31/12/2021";

    # Remuneração
    private $calculoInicial = "Média aritmética simples dos 80% das maiores remunerações de contribuições corrigidas desde julho/94 - Proporcional ao tempo de contribuição - Lei Federal 10.887";
    private $teto = "Remuneração do servidor no cargo efetivo";
    private $reajuste = "INPC – Aplicado em Janeiro – Lei 6.244/2012";
    private $paridade = "SEM PARIDADE";

    # Descrições
    private $idadeDescricao = "Idade do servidor.";
    private $tempoPublicoDescicao = "Tempo de todos os periodo públicos ininterruptos.";
    private $tempoCargoDescicao = "Tempo no mesmo órgão e mesmo cargo.";
    private $dtRequesitosCumpridosDescicao = "Data limite para o cumprimento dos requesito.";

    # Dados do servidor
    public $analiseIdade = null;
    public $analisePublico = null;
    public $analiseCargoEfetivo = null;
    public $analiseDtRequesitosCumpridos = null;

    # Variaveis de Retorno
    public $dataCriterioIdade = null;
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

        $tempoTotal = $tempoAverbadoPublico + $tempoAverbadoPrivado + $tempoUenf;
        $tempoPublicoIninterrupto = $aposentadoria->get_tempoPublicoIninterrupto($this->idServidor);

        if ($sexo == "Masculino") {
            $regraIdade = $this->idadeHomem;
        } else {
            $regraIdade = $this->idadeMulher;
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
            $this->analiseIdade = "Ainda faltam<br/>".dataDif(date("d/m/Y"), $this->dataCriterioIdade)." dias.";
        }

        # Serviço Público Initerrupto
        $resta2 = ($this->servicoPublico * 365) - $tempoPublicoIninterrupto;
        $this->dataCriterioTempoServicoPublico = addDias($hoje, $resta2, false);  // retiro a contagem do primeiro dia para não contar hoje 2 vezes
        if ($tempoPublicoIninterrupto >= ($this->servicoPublico * 365)) {
            $this->analisePublico = "OK";
        } else {
            $this->analisePublico = "Ainda faltam<br/>{$resta2} dias.";
        }

        # Cargo Efetivo
        $resta3 = ($this->cargoEfetivo * 365) - $tempoUenf;
        $this->dataCriterioTempoCargo = addDias($hoje, $resta3, false);  // retiro a contagem do primeiro dia para não contar hoje 2 vezes
        if ($tempoUenf >= ($this->cargoEfetivo * 365)) {
            $this->analiseCargoEfetivo = "OK";
        } else {
            $this->analiseCargoEfetivo = "Ainda faltam<br/>{$resta3} dias.";
        }

        # Data do Direito a Aposentadoria
        $this->dataDireitoAposentadoria = dataMaiorArray([
            $this->dataCriterioIdade,
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

    public function exibeAnalise($relatorio = false) {

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
        } else {
            $regraIdade = $this->idadeMulher;
        }

        /*
         *  Tabela
         */

        $array = [
            ["Idade", $this->idadeDescricao, "{$regraIdade} anos", "{$idadeServidor} anos", $this->dataCriterioIdade, $this->analiseIdade],
            ["Serviço Público", $this->tempoPublicoDescicao, "{$this->servicoPublico} anos<br/>(" . ($this->servicoPublico * 365) . " dias)", "{$tempoPublicoIninterrupto} dias", $this->dataCriterioTempoServicoPublico, $this->analisePublico],
            ["Cargo Efetivo", $this->tempoCargoDescicao, "{$this->cargoEfetivo} anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)", "{$tempoUenf} dias", $this->dataCriterioTempoCargo, $this->analiseCargoEfetivo],
            ["Data Limite", $this->dtRequesitosCumpridosDescicao, $this->dtRequesitosCumpridos, $this->dataDireitoAposentadoria, "-", $this->analiseDtRequesitosCumpridos],
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
    }

    ###########################################################

    public function exibeAnaliseResumo($relatorio = false) {

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
            $cor = "alert";
        }

        # Exibe o resumo
        if ($relatorio) {
            return $texto;
        } else {
            
            $painel = new Callout($cor);
            $painel->abre();

            p($texto, "center");

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
            ["<p id='pLinha1'>Idade</p><p id='pLinha4'>{$this->idadeDescricao}</p>", $this->idadeMulher . " anos", $this->idadeHomem . " anos"],
            ["<p id='pLinha1'>Serviço Público</p><p id='pLinha4'>{$this->tempoPublicoDescicao}</p>", $this->servicoPublico . " anos<br/>(" . ($this->servicoPublico * 365) . " dias)", $this->servicoPublico . " anos<br/>(" . ($this->servicoPublico * 365) . " dias)"],
            ["<p id='pLinha1'>Cargo Efetivo</p><p id='pLinha4'>{$this->tempoCargoDescicao}</p>", $this->cargoEfetivo . " anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)", $this->cargoEfetivo . " anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)"],
            ["<p id='pLinha1'>Data Limite</p><p id='pLinha4'>{$this->dtRequesitosCumpridosDescicao}</p>", $this->dtRequesitosCumpridos, $this->dtRequesitosCumpridos],
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
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo("Remuneração");
        }

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
}
