<?php

class AposentadoriaTransicao2 {

    /**
     * Abriga as várias rotina referentes a aposentadoria do servidor
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */

    # Regras
    private $dtIngresso = "16/11/1998";
    private $contribuicaoHomem = 35;
    private $contribuicaoMulher = 30;
    private $idadeHomem = 60;
    private $idadeMulher = 55;
    private $servicoPublico = 25;
    private $cargoEfetivo = 5;
    private $tempoCarreira = 15;

    # Remuneração
    private $calculoInicial = "Última remuneração";
    private $teto = "Remuneração do servidor no cargo efetivo";
    private $reajuste = "Na mesma data e índice dos servidores ativos";
    private $paridade = "COM PARIDADE";

    ###########################################################

    public function __construct() {

        /**
         * Inicia a classe
         */
    }

    ###########################################################

    public function exibeAnalise($idServidor) {

        # Pega os dados do servidor
        $pessoal = new Pessoal();
        $idadeServidor = $pessoal->get_idade($idServidor);
        $sexo = $pessoal->get_sexo($idServidor);
        $dtAdmissao = $pessoal->get_dtAdmissao($idServidor);

        # Pega o tempo de contribuicao
        $tempoTotal = $this->getTempoContribuicao($idServidor);

        # Pega a data de Ingresso
        $aposentadoria = new Aposentadoria();
        $dtIngressoServidor = $aposentadoria->get_dtIngresso($idServidor);

        # Pega o tempo no cargo efetivo
        $tempoUenf = $aposentadoria->get_tempoServicoUenf($idServidor);

        # Pega o tempo ininterrupto
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
        if (strtotime(date_to_bd($dtIngressoServidor)) < strtotime(date_to_bd($this->dtIngresso))) {
            $analiseIngresso = "OK";
            
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

            # Tempo na Carreira
            # Existe um entendimento que o tempo de carreira é o tempo no mesmo órgão e o mesmo tipo de cargo
            $carreira = $tempoUenf;
            if ($carreira >= ($this->tempoCarreira * 365)) {
                $analiseCarreira = "OK";
            } else {
                $resta = ($this->tempoCarreira * 365) - $carreira;
                $dtFutura = addDias($hoje, $resta);
                $analiseCarreira = "Ainda faltam {$resta} dias<br/>Somente em {$dtFutura}.";
            }

            /*
             * Descrição
             */

            $dtIngressoDescricao = "Data de entrada no serviço público sem interrupção.";
            $tempoContribuiçãoDescricao = "Tempo Total averbado (público e privado).";
            $idadeDescricao = "Idade do servidor.";
            $tempoPublicoDescricao = "Tempo de todos os periodo públicos ininterruptos.";
            $tempoCargoDescricao = "Tempo no mesmo órgão e mesmo cargo.";
            $tempoCarreiraDescricao = "Tempo no mesmo órgão e mesmo tipo de cargo.";

            /*
             *  Tabela
             */

            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            tituloTable("ART. 3º. DA EC Nº. 47/2005");
            callout("É o benefício aos servidores que ingressaram no serviço público até 16 de dezembro de 1998.");

            $grid->fechaColuna();
            $grid->abreColuna(8);

            $array = [
                ["Data de Ingresso", $dtIngressoDescricao, "até {$this->dtIngresso}", $dtIngressoServidor, $analiseIngresso],
                ["Contribuição", $tempoContribuiçãoDescricao, "{$contribuicaoRegra} anos<br/>(" . ($contribuicaoRegra * 365) . " dias)", "{$tempoTotal} dias", $analiseContribuicao],
                ["Idade", $idadeDescricao, "{$idadeRegra} anos", "{$idadeServidor} anos", $analiseIdade],
                ["Serviço Público", $tempoPublicoDescricao, "{$this->servicoPublico} anos<br/>(" . ($this->servicoPublico * 365) . " dias)", "{$tempoPublicoIninterrupto} dias", $analisePublico],
                ["Cargo Efetivo", $tempoCargoDescricao, "{$this->cargoEfetivo} anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)", "{$tempoUenf} dias", $analiseCargoEfetivo],
                ["Tempo na Carreira", $tempoCarreiraDescricao, "$this->tempoCarreira  anos<br/>(" . ($this->tempoCarreira * 365) . " dias)", "{$carreira} dias)", $analiseCarreira],
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
        } else {
            # Somente servidores que ingressaram até a data especificada
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            tituloTable("ART. 3º. DA EC Nº. 47/2005");
            callout("É o benefício aos servidores que ingressaram no serviço público até 16 de dezembro de 1998.");
            br();
            
            p("Data de Ingresso: {$dtIngressoServidor}<br/>Não tem direito a esta modalidade de aposentadoria.","center","f14");
            
            $grid->fechaColuna();
            $grid->fechaGrid();
        }
    }
    
    ###########################################################

    public function getDataAposentadoria($idServidor) {

        # Pega os dados do servidor
        $pessoal = new Pessoal();
        $idadeServidor = $pessoal->get_idade($idServidor);
        $sexo = $pessoal->get_sexo($idServidor);
        $dtAdmissao = $pessoal->get_dtAdmissao($idServidor);

        # Pega o tempo de contribuicao
        $tempoTotal = $this->getTempoContribuicao($idServidor);

        # Pega a data de Ingresso
        $aposentadoria = new Aposentadoria();
        $dtIngressoServidor = $aposentadoria->get_dtIngresso($idServidor);

        # Pega o tempo no cargo efetivo
        $tempoUenf = $aposentadoria->get_tempoServicoUenf($idServidor);

        # Pega o tempo ininterrupto
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
        if (strtotime(date_to_bd($dtIngressoServidor)) < strtotime(date_to_bd($this->dtIngresso))) {
            # Esta rotina só serve para servidores que atendem a data de ingresso
        } else {
            # Se não atende retorna informando que não tem direito
            return "Não pode solicitar essa opção";
        }

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

        # Tempo na Carreira
        # Existe um entendimento que o tempo de carreira é o tempo no mesmo órgão e o mesmo tipo de cargo
        $carreira = $tempoUenf;
        if ($carreira >= ($this->tempoCarreira * 365)) {
            $dataCarreira = null;
        } else {
            $resta = ($this->tempoCarreira * 365) - $carreira;
            $dataCarreira = addDias($hoje, $resta);
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

        # Agora com a data de carreira
        if (!is_null($dataCargo)) {
            $dtRetorno = dataMaior($dtRetorno, $dataCarreira);
        }

        return $dtRetorno;
    }

    ###########################################################

    public function getDiasFaltantes($idServidor) {

        # Pega a data de aposentadoria
        $dtAposent = $this->getDataAposentadoria($idServidor);

        # Verifica se retornou data
        if ($dtAposent == "Não pode solicitar essa opção") {
            return "---";
        }

        # Verifica se ja passou
        if (jaPassou($dtAposent)) {
            return 0;
        } else {
            return dataDif(date("d/m/Y"), $dtAposent);
        }
    }

    ###########################################################

    public function exibeRegras() {

        # Exibe outras informações
        $array = [
            ["Ingresso", "até " . $this->dtIngresso, "até " . $this->dtIngresso],
            ["Idade", $this->idadeMulher . " anos", $this->idadeHomem . " anos"],
            ["Contribuição", $this->contribuicaoMulher . " anos<br/>(" . ($this->contribuicaoMulher * 365) . " dias)", $this->contribuicaoHomem . " anos<br/>(" . ($this->contribuicaoHomem * 365) . " dias)"],
            ["Serviço Público", $this->servicoPublico . " anos<br/>(" . ($this->servicoPublico * 365) . " dias)", $this->servicoPublico . " anos<br/>(" . ($this->servicoPublico * 365) . " dias)"],
            ["Carreira", $this->tempoCarreira . " anos<br/>(" . ($this->tempoCarreira * 365) . " dias)", $this->tempoCarreira . " anos<br/>(" . ($this->tempoCarreira * 365) . " dias)"],
            ["Cargo Efetivo", $this->cargoEfetivo . " anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)", $this->cargoEfetivo . " anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)"],
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

    public function getTempoContribuicao($idServidor) {

        # Pega os tempos averbados
        $averbacao = new Averbacao();
        $tempoAverbadoPublico = $averbacao->get_tempoAverbadoPublico($idServidor);
        $tempoAverbadoPrivado = $averbacao->get_tempoAverbadoPrivado($idServidor);

        # Prga o tempo Uenf
        $aposentadoria = new Aposentadoria();
        $tempoUenf = $aposentadoria->get_tempoServicoUenf($idServidor);

        # Retorna o tempo total em dias
        return $tempoAverbadoPublico + $tempoAverbadoPrivado + $tempoUenf;
    }
}
