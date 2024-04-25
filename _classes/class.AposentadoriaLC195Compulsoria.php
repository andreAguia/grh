<?php

class AposentadoriaLC195Compulsoria {

    /**
     * Abriga as rotina referentes a aposentadoria do servidor
     * Direito adquirido
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    # Id Servidor
    private $idServidor = null;

    # Descricao
    private $tipo = "Regra Permanente";
    private $descricao = "Aposentadoria Compulsória por Idade";
    private $legislacao = "Art. 2º, inciso II, da Lei Complementar nº 195/2021";

    /*
     * Regras
     */
    # Data de Ingresso
    private $dtIngresso = null;

    # Data Limite do direito
    private $dtRequesitosCumpridos = null;
    private $dtRequesitosCumpridosDescicao = "Data limite para o cumprimento dos requesito.";

    # Idade
    private $idadeHomem = 75;
    private $idadeMulher = 75;
    private $idadeDescricao = "Idade do servidor.";

    # Tempo de Contrivuição
    private $contribuicaoHomem = null;
    private $contribuicaoMulher = null;
    private $tempoContribuiçãoDescricao = "Tempo Total averbado (público e privado).";

    # Tempo de Serviço Público
    private $servicoPublico = null;
    private $tempoPublicoDescicao = "Tempo de todos os periodo públicos ininterruptos.";

    # Tempo no cargo efetivo
    private $cargoEfetivo = null;
    private $tempoCargoDescicao = "Tempo no mesmo órgão e mesmo cargo.";

    # Remuneração
    private $calculoInicial = "Média aritmética simples de TODAS as remunerações a partir de julho de 1994 - Lei Federal 10.887";
    private $percentualDevido = "60% + 2% para cada ano que exceder 20 anos de contribuição";
    private $reajuste = "INPC – Lei 6.244/2012";
    private $paridade = "SEM PARIDADE";

    ###########################################################    
    # Dados do servidor
    private $analiseIdade = null;
    private $analiseContribuicao = null;
    private $analisePublico = null;
    private $analiseCargoEfetivo = null;
    private $analiseDtRequesitosCumpridos = null;

    # Variaveis de Retorno
    private $dataCriterioIdade = null;
    private $dataCriterioTempoContribuicao = null;
    private $dataCriterioTempoServicoPublico = null;
    private $dataCriterioTempoCargo = null;
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

        if (!empty($idServidor)) {
            $this->idServidor = $idServidor;
        }

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
            $this->analiseIdade = "Ainda faltam<br/>" . dataDif(date("d/m/Y"), $this->dataCriterioIdade) . " dias.";
        }

        # Data do Direito a Aposentadoria
        $this->dataDireitoAposentadoria = $this->dataCriterioIdade;

        # Define o texto de retorno    
        if ($this->analiseDtRequesitosCumpridos == "OK" OR $this->dtRequesitosCumpridos == null) {
            if (jaPassou($this->dataDireitoAposentadoria)) {
                $this->textoRetorno = "O Servidor tem direito a esta modalidade de aposentadoria desde:<br/><b>{$this->dataDireitoAposentadoria}</b>";
                $this->textoReduzido = "Desde:<br/><b>{$this->dataDireitoAposentadoria}</b>";
                $this->corFundo = "success";
            } else {
                $this->textoRetorno = "O Servidor terá direito a esta modalidade de aposentadoria em:<br/><b>{$this->dataDireitoAposentadoria}</b>";
                $this->textoReduzido = "Somente em:<br/><b>{$this->dataDireitoAposentadoria}</b>";
                $this->corFundo = "warning";
            }
        } else {
            $this->textoRetorno = "O Servidor <b>Não Tem Direito</b><br/>a essa modalidade de aposentadoria.";
            $this->textoReduzido = "<b>Não Tem Direito</b>";
            $this->corFundo = "alert";
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
            $regraContribuicao = $this->contribuicaoHomem;
        } else {
            $regraIdade = $this->idadeMulher;
            $regraContribuicao = $this->contribuicaoMulher;
        }

        /*
         *  Tabela
         */

        $array = [
            ["Idade", $this->idadeDescricao, "{$regraIdade} anos", "{$idadeServidor} anos", $this->dataCriterioIdade, $this->analiseIdade]
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
            ["<p id='pLinha1'>Idade</p><p id='pLinha4'>{$this->idadeDescricao}</p>", $this->idadeMulher . " anos", $this->idadeHomem . " anos"]];

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
            ["Percentual Devido", $this->percentualDevido],
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
        $tabela->set_label(["Item", "Descrição"]);
        $tabela->set_width([30, 70]);
        $tabela->set_align(["left", "left"]);
        $tabela->set_totalRegistro(false);
        $tabela->show();
    }

    ###########################################################

    public function exibeResumoCartilha($numero = 1) {

        $figura = new Imagem(PASTA_FIGURAS . "lc195compulsoria{$numero}.png", null, "100%", "100%");
        $figura->set_id('imgCasa');
        $figura->set_class('imagem');
        $figura->show();
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
        $link = "?fase=carregarPagina&id={$idServidor}&link=compulsoria";
        
        echo "<a href='{$link}'>";

        # Exibe o resumo
        $painel = new Callout($this->corFundo);
        $painel->abre();
        p($this->textoReduzido, "center");
        $painel->fecha();
        
        echo "</a>";
    }

    ###########################################################
}
