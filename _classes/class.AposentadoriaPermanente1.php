<?php

class AposentadoriaPermanente1 {

    /**
     * Abriga as várias rotina referentes a aposentadoria do servidor
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    # Regras
    private $dtIngresso = "31/12/2003";
    private $contribuicaoHomem = 35;
    private $contribuicaoMulher = 30;
    private $idadeHomem = 60;
    private $idadeMulher = 55;
    private $servicoPublico = 10;
    private $cargoEfetivo = 5;

    # Remuneração
    private $calculoInicial = "Média aritmética simples das 80% maiores remunerações de contribuição";
    private $teto = "Remuneração do servidor no cargo efetivo";
    private $reajuste = "INPC – LEI 6.244/2012";
    private $paridade = "SEM PARIDADE";

    # Descrições
    private $dtIngressoDescricao = "Data de entrada no serviço público sem interrupção.";
    private $tempoContribuiçãoDescricao = "Tempo Total averbado (público e privado).";
    private $idadeDescricao = "Idade do servidor.";
    private $tempoPublicoDescicao = "Tempo de todos os periodo públicos ininterruptos.";
    private $tempoCargoDescicao = "Tempo no mesmo órgão e mesmo cargo.";

    ###########################################################

    public function __construct() {

        /**
         * Inicia a classe
         */
    }

    ###########################################################

    public function exibeAnalise($idServidor = null) {

        # Pega os dados do servidor
        $pessoal = new Pessoal();
        $idadeServidor = $pessoal->get_idade($idServidor);
        $sexo = $pessoal->get_sexo($idServidor);
        $dtAdmissao = $pessoal->get_dtAdmissao($idServidor);

        $averbacao = new Averbacao();
        $tempoAverbadoPublico = $averbacao->get_tempoAverbadoPublico($idServidor);
        $tempoAverbadoPrivado = $averbacao->get_tempoAverbadoPrivado($idServidor);

        $aposentadoria = new Aposentadoria();
        $tempoUenf = $aposentadoria->get_tempoServicoUenf($idServidor);
        $dtIngressoServidor = $aposentadoria->get_dtIngresso($idServidor);

        $tempoTotal = $tempoAverbadoPublico + $tempoAverbadoPrivado + $tempoUenf;
        $tempoPublicoIninterrupto = $aposentadoria->get_tempoPublicoIninterrupto($idServidor);

        if ($sexo == "Masculino") {
            $idadeRegra = $this->idadeHomem;
            $contribuicaoRegra = $this->contribuicaoHomem;
        } else {
            $idadeRegra = $this->idadeMulher;
            $contribuicaoRegra = $this->contribuicaoMulher;
        }

        $hoje = date("d/m/Y");

        /*
         *  Análise
         */

        # Data de Ingresso
        if (strtotime(date_to_bd($dtIngressoServidor)) >= strtotime(date_to_bd($this->dtIngresso))) {
            $analiseIngresso = "OK";
        } else {
            # Qualquer servidor pode optar por essa modadalidade
            $analiseIngresso = "OK";
        }

        # Tempo de Contribuição
        if ($tempoTotal >= ($contribuicaoRegra * 365)) {
            $analiseContribuicao = "OK";
        } else {
            $resta = ($contribuicaoRegra * 365) - $tempoTotal;
            $dtFutura = addDias($hoje, $resta);
            $analiseContribuicao = "Ainda faltam {$resta} dias<br/>Somente em {$dtFutura}.";
        }

        # Idade
        if ($idadeServidor >= $idadeRegra) {
            $analiseIdade = "OK";
        } else {
            # Pega a data de nascimento (vem dd/mm/AAAA)
            $dtNasc = $pessoal->get_dataNascimento($idServidor);

            # Calcula a data
            $novaData = addAnos($dtNasc, $idadeRegra);
            $analiseIdade = "Somente em {$novaData}.";
        }

        # Serviço Público Initerrupto
        if ($tempoPublicoIninterrupto >= ($this->servicoPublico * 365)) {
            $analisePublico = "OK";
        } else {
            $resta = ($this->servicoPublico * 365) - $tempoPublicoIninterrupto;
            $dtFutura = addDias($hoje, $resta);
            $analisePublico = "Ainda faltam {$resta} dias<br/>Somente em {$dtFutura}.";
        }

        # Cargo Efetivo
        if ($tempoUenf >= ($this->cargoEfetivo * 365)) {
            $analiseCargoEfetivo = "OK";
        } else {
            $resta = ($this->cargoEfetivo * 365) - $tempoUenf;
            $dtFutura = addDias($hoje, $resta);
            $analiseCargoEfetivo = "Ainda faltam {$resta} dias<br/>Somente em {$dtFutura}.";
        }

        /*
         * Descrição
         */



        /*
         *  Tabela
         */

        # Limita o tamanho da tela
        $grid = new Grid();
        $grid->abreColuna(12);

        tituloTable("Aposentadoria Voluntária por Idade e Tempo de Contribuição");
        br();

        $grid->fechaColuna();
        $grid->abreColuna(8);

        $array = [
            ["Contribuição", $this->tempoContribuiçãoDescricao, "{$contribuicaoRegra} anos<br/>(" . ($contribuicaoRegra * 365) . " dias)", "{$tempoTotal} dias", $analiseContribuicao],
            ["Idade", $this->idadeDescricao, "{$idadeRegra} anos", "{$idadeServidor} anos", $analiseIdade],
            ["Serviço Público", $this->tempoPublicoDescicao, "{$this->servicoPublico} anos<br/>(" . ($this->servicoPublico * 365) . " dias)", "{$tempoPublicoIninterrupto} dias", $analisePublico],
            ["Cargo Efetivo", $this->tempoCargoDescicao, "{$this->cargoEfetivo} anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)", "{$tempoUenf} dias", $analiseCargoEfetivo],
        ];

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_titulo("Requisitos");
        $tabela->set_conteudo($array);
        $tabela->set_label(array("Item", "Descrição", "Regra", "Servidor", "Análise"));
        $tabela->set_width(array(20, 25, 15, 15, 25));
        $tabela->set_align(array("left", "left"));
        $tabela->set_totalRegistro(false);
        $tabela->set_formatacaoCondicional(array(
            array('coluna' => 4,
                'valor' => 'OK',
                'operador' => '=',
                'id' => 'emAberto'),
            array('coluna' => 4,
                'valor' => 'OK',
                'operador' => '<>',
                'id' => 'arquivado')
        ));
        $tabela->show();

        $grid->fechaColuna();
        $grid->abreColuna(4);

        # Exibe outras informações
        $array = [
            ["Cálculo Inicial", $this->calculoInicial],
            ["Teto", $this->teto],
            ["Reajuste", $this->reajuste],
            ["Paridade", $this->paridade]
        ];

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_titulo("Remuneração");
        $tabela->set_conteudo($array);
        $tabela->set_label(array("Item", "Descrição"));
        $tabela->set_width(array(30, 70));
        $tabela->set_align(array("left", "left"));
        $tabela->set_totalRegistro(false);
        $tabela->show();

        $grid->fechaColuna();
        $grid->fechaGrid();
    }

    ###########################################################

    public function getDataAposentadoria($idServidor) {

        # Pega os dados do servidor
        $pessoal = new Pessoal();
        $idadeServidor = $pessoal->get_idade($idServidor);
        $sexo = $pessoal->get_sexo($idServidor);
        $dtAdmissao = $pessoal->get_dtAdmissao($idServidor);

        $averbacao = new Averbacao();
        $tempoAverbadoPublico = $averbacao->get_tempoAverbadoPublico($idServidor);
        $tempoAverbadoPrivado = $averbacao->get_tempoAverbadoPrivado($idServidor);

        $aposentadoria = new Aposentadoria();
        $tempoUenf = $aposentadoria->get_tempoServicoUenf($idServidor);
        $dtIngressoServidor = $aposentadoria->get_dtIngresso($idServidor);

        $tempoTotal = $tempoAverbadoPublico + $tempoAverbadoPrivado + $tempoUenf;
        $tempoPublicoIninterrupto = $aposentadoria->get_tempoPublicoIninterrupto($idServidor);

        if ($sexo == "Masculino") {
            $idadeRegra = $this->idadeHomem;
            $contribuicaoRegra = $this->contribuicaoHomem;
        } else {
            $idadeRegra = $this->idadeMulher;
            $contribuicaoRegra = $this->contribuicaoMulher;
        }

        $hoje = date("d/m/Y");

        /*
         *  Análise
         */

        # Idade
        $dtNasc = $pessoal->get_dataNascimento($idServidor);
        $dataIdade = addAnos($dtNasc, $idadeRegra);

        # Tempo de Contribuição
        if ($tempoTotal >= ($contribuicaoRegra * 365)) {
            $dataContribuicao = null;
        } else {
            $resta = ($contribuicaoRegra * 365) - $tempoTotal;
            $dataContribuicao = addDias($hoje, $resta);
        }

        # Serviço Público Initerrupto
        if ($tempoPublicoIninterrupto >= ($this->servicoPublico * 365)) {
            $dataPublico = null;
        } else {
            $resta = ($this->servicoPublico * 365) - $tempoPublicoIninterrupto;
            $dataPublico = addDias($hoje, $resta);
        }

        # Cargo Efetivo
        if ($tempoUenf >= ($this->cargoEfetivo * 365)) {
            $dataCargo = null;
        } else {
            $resta = ($this->cargoEfetivo * 365) - $tempoUenf;
            $dataCargo = addDias($hoje, $resta);
        }

        /*
         * Verifica a data maior
         */

        # Compara com a idade
        $dtRetorno = dataMaior($dtAdmissao, $dataIdade);

        # Agora com a data de contribuição
        if (!is_null($dataContribuicao)) {
            $dtRetorno = dataMaior($dtRetorno, $dataContribuicao);
        }

        # Agora com a data de Serviço Público Initerrupto
        if (!is_null($dataPublico)) {
            $dtRetorno = dataMaior($dtRetorno, $dataPublico);
        }

        # Agora com a data de cargo efetivo
        if (!is_null($dataCargo)) {
            $dtRetorno = dataMaior($dtRetorno, $dataCargo);
        }

        return $dtRetorno;
    }

    ###########################################################

    public function getDiasFaltantes($idServidor) {

        # Pega a data de aposentadoria
        $dtAposent = $this->getDataAposentadoria($idServidor);

        # Verifica se ja passou
        if (jaPassou($dtAposent)) {
            return "0";
        } else {
            return dataDif(date("d/m/Y"), $dtAposent);
        }
    }

    ###########################################################

    public function exibeRegras() {

        # Exibe outras informações
        $array = [
            ["<span data-tooltip title='{$this->idadeDescricao}'>Idade</span>", $this->idadeMulher . " anos", $this->idadeHomem . " anos"],
            ["<span data-tooltip title='{$this->tempoContribuiçãoDescricao}'>Contribuição</span>", $this->contribuicaoMulher . " anos<br/>(" . ($this->contribuicaoMulher * 365) . " dias)", $this->contribuicaoHomem . " anos<br/>(" . ($this->contribuicaoHomem * 365) . " dias)"],
            ["<span data-tooltip title='{$this->tempoPublicoDescicao}'>Serviço Público</span>", $this->servicoPublico . " anos<br/>(" . ($this->servicoPublico * 365) . " dias)", $this->servicoPublico . " anos<br/>(" . ($this->servicoPublico * 365) . " dias)"],
            ["<span data-tooltip title='{$this->tempoCargoDescicao}'>Cargo Efetivo</span>", $this->cargoEfetivo . " anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)", $this->cargoEfetivo . " anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)"],
        ];

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_titulo("Regras Gerais");
        $tabela->set_conteudo($array);
        $tabela->set_label(array("Requisito", "Mulher", "Homem"));
        $tabela->set_width(array(30, 35, 35));
        $tabela->set_align(array("left"));
        $tabela->set_totalRegistro(false);
        $tabela->show();
    }

    ###########################################################

    public function exibeDados($idServidor) {

        # Verifica se foi informado do id
        if (empty($idServidor)) {
            return null;
        } else {
            # Abre as classes
            $pessoal = new Pessoal();
            $aposentadoria = new Aposentadoria();

            # Idade
            p("Idade: " . $pessoal->get_idade($idServidor), "pLinha1");

            # Contribuição
            p("Contribuição: " . $aposentadoria->get_tempoTotal($idServidor) . " dias", "pLinha1");

            # Serviço Público
            p("Serviço Público: " . $aposentadoria->get_tempoServicoUenf($idServidor) . " dias", "pLinha1");

            # Serviço Público
            p("Cargo Efetivo: " . $aposentadoria->get_tempoPublicoIninterrupto($idServidor) . " dias", "pLinha1");
        }
    }

}
