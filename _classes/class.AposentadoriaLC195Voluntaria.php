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

    # Descricao
    private $tipo = "Regra Permanente";
    private $descricao = "Aposentadoria Voluntária por Idade e Tempo de Contribuição";
    private $legislacao = "Art. 2º, inciso III, da Lei Complementar nº 195/2021";

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

        # Tempo de Contribuição
        $resta1 = ($regraContribuicao * 365) - $tempoTotal;
        $this->dataCriterioTempoContribuicao = addDias($hoje, $resta1, false);  // retiro a contagem do primeiro dia para não contar hoje 2 vezes
        if ($tempoTotal >= ($regraContribuicao * 365)) {
            $this->analiseContribuicao = "OK";
        } else {
            $this->analiseContribuicao = "Ainda faltam<br/>{$resta1} dias.";
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
            $this->dataCriterioTempoContribuicao,
            $this->dataCriterioTempoServicoPublico,
            $this->dataCriterioTempoCargo
        ]);

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
            ["Idade", $this->idadeDescricao, "{$regraIdade} anos", "{$idadeServidor} anos", $this->dataCriterioIdade, $this->analiseIdade],
            ["Contribuição", $this->tempoContribuiçãoDescricao, "{$regraContribuicao} anos<br/>(" . ($regraContribuicao * 365) . " dias)", "{$tempoTotal} dias", $this->dataCriterioTempoContribuicao, $this->analiseContribuicao],
            ["Serviço Público", $this->tempoPublicoDescicao, "{$this->servicoPublico} anos<br/>(" . ($this->servicoPublico * 365) . " dias)", "{$tempoPublicoIninterrupto} dias", $this->dataCriterioTempoServicoPublico, $this->analisePublico],
            ["Cargo Efetivo", $this->tempoCargoDescicao, "{$this->cargoEfetivo} anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)", "{$tempoUenf} dias", $this->dataCriterioTempoCargo, $this->analiseCargoEfetivo],
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
            ["<p id='pLinha1'>Idade</p><p id='pLinha4'>{$this->idadeDescricao}</p>", $this->idadeMulher . " anos", $this->idadeHomem . " anos"],
            ["<p id='pLinha1'>Contribuição</p><p id='pLinha4'>{$this->tempoContribuiçãoDescricao}</p>", $this->contribuicaoMulher . " anos<br/>(" . ($this->contribuicaoMulher * 365) . " dias)", $this->contribuicaoHomem . " anos<br/>(" . ($this->contribuicaoHomem * 365) . " dias)"],
            ["<p id='pLinha1'>Serviço Público</p><p id='pLinha4'>{$this->tempoPublicoDescicao}</p>", $this->servicoPublico . " anos<br/>(" . ($this->servicoPublico * 365) . " dias)", $this->servicoPublico . " anos<br/>(" . ($this->servicoPublico * 365) . " dias)"],
            ["<p id='pLinha1'>Cargo Efetivo</p><p id='pLinha4'>{$this->tempoCargoDescicao}</p>", $this->cargoEfetivo . " anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)", $this->cargoEfetivo . " anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)"],];

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
        $link = "?fase=carregarPagina&id={$idServidor}&link=voluntaria";
        
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
