<?php

class PrevisaoAposentadoria {

    /**
     * Abriga a classe de Previsão de APosentadoria
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    # Id Servidor
    private $idServidor = null;

    # Descricao
    private $tipo = null;
    private $descricao = null;
    private $legislacao = null;

    # Data de Ingresso
    private $dtIngresso = null;

    # Data Limite do direito
    private $dtRequesitosCumpridos = null;

    # Regras Gerais    
    private $idadeHomem = null;
    private $idadeMulher = null;
    private $contribuicaoHomem = null;
    private $contribuicaoMulher = null;
    private $servicoPublico = null;
    private $cargoEfetivo = null;

    # Remuneração
    private $calculoInicial = null;
    private $percentualDevido = null;
    private $teto = null;
    private $reajuste = null;
    private $paridade = null;

    # Data da Lei - Só pode aposentar apos essa data
    private $dataLei = null;
    private $ajustado = false;

    # Cartilhas
    private $cartilha1 = null;
    private $cartilha2 = null;

    # Pontuação
    private $pontosHomem = null;
    private $pontosMulher = null;
    private $tabelaM = null;
    private $tabelaF = null;

    # Descrições
    private $dtIngressoDescricao = "Data de ingresso no serviço público sem interrupção.";
    private $tempoContribuiçãoDescricao = "Tempo Total averbado<br/>(público e privado).";
    private $idadeDescricao = "Idade do servidor.";
    private $tempoPublicoDescicao = "Tempo de todos os periodo públicos ininterruptos.";
    private $tempoCargoDescicao = "Tempo no mesmo órgão e mesmo cargo.";
    private $dtRequesitosCumpridosDescicao = "Data limite para o cumprimento dos requesito.";
    private $pontuacaoInicialDescricao = "Pontuação Inicial.";
    private $pedagioDescricao = "Período adicional de contribuição calculado apartir do tempo de contribuição que faltava ao servidor em 01/01/2021";

    # Relatório
    private $mensagemRelatorio = "Atenção, esta é uma previsão da posentadoria e as informações aqui contidas podem variar com o tempo.";

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

    # Verifica se compara com a compulsória
    private $verificaCompulsoria = true;

    ###########################################################

    public function __construct($modalidade = null) {

        # Preenche as variáveis de acordo com o tipo de aposentadoria
        switch ($modalidade) {
            
            ######################################

            case "permanente1" :

                # Descrição
                $this->tipo = "Regra Permanente";
                $this->descricao = "Aposentadoria Voluntária por Idade e Tempo de Contribuição";
                $this->legislacao = "Art. 2º, inciso III, da Lei Complementar nº 195/2021";

                # Regras
                $this->dtRequesitosCumpridos = null;
                $this->idadeHomem = 65;
                $this->idadeMulher = 62;
                $this->contribuicaoHomem = 25;
                $this->contribuicaoMulher = 25;
                $this->servicoPublico = 10;
                $this->cargoEfetivo = 5;

                # Remuneração
                $this->calculoInicial = "Média aritmética simples de TODAS as remunerações a partir de julho de 1994 - Lei Federal 10.887";
                $this->percentualDevido = "60% + 2% para cada ano que exceder 20 anos de contribuição";
                $this->reajuste = "INPC – Lei 6.244/2012";
                $this->paridade = "SEM PARIDADE";

                # Data da Lei - Só pode aposentar apos essa data
                $this->dataLei = "01/01/2022";

                # Cartilha
                $this->cartilha1 = "lc195voluntaria1.png";
                $this->cartilha2 = "lc195voluntaria2.png";
                break;
            
            ######################################

            case "permanente2" :
            case "compulsoria" :

                # Descrição
                $this->tipo = "Regra Permanente";
                $this->descricao = "Aposentadoria Compulsória por Idade";
                $this->legislacao = "Art. 2º, inciso II, da Lei Complementar nº 195/2021";

                # Regras
                $this->dtRequesitosCumpridos = null;
                $this->idadeHomem = 75;
                $this->idadeMulher = 75;
                $this->contribuicaoHomem = null;
                $this->contribuicaoMulher = null;
                $this->servicoPublico = null;
                $this->cargoEfetivo = null;

                # Remuneração
                $this->calculoInicial = "Média aritmética simples de TODAS as remunerações a partir de julho de 1994 - Lei Federal 10.887";
                $this->percentualDevido = "60% + 2% para cada ano que exceder 20 anos de contribuição";
                $this->reajuste = "INPC – Lei 6.244/2012";
                $this->paridade = "SEM PARIDADE";

                # Data da Lei - Só pode aposentar apos essa data
                $this->dataLei = null;

                # Retira a verificação da compulsória
                $this->verificaCompulsoria = false;

                # Cartilha
                $this->cartilha1 = "lc195compulsoria1.png";
                $this->cartilha2 = "lc195compulsoria2.png";
                break;
            
            ######################################3

            case "transicao1" :

                # Descrição
                $this->tipo = "Regra de Transição";
                $this->descricao = "Aposentadoria por Idade e Tempo de Contribuição<br/>Regra dos Pontos - Integralidade e Paridade";
                $this->legislacao = "Art. 2º, inciso II, da Lei Complementar nº 195/2021";

                # Regras
                $this->dtRequesitosCumpridos = null;
                $this->idadeHomem = 65;
                $this->idadeMulher = 62;
                $this->contribuicaoHomem = 35;
                $this->contribuicaoMulher = 30;
                $this->servicoPublico = 20;
                $this->cargoEfetivo = 5;

                # Remuneração
                $this->calculoInicial = "Última remuneração";
                $this->teto = "Remuneração do servidor no cargo efetivo";
                $this->reajuste = "Na mesma data e índice dos servidores ativos";
                $this->paridade = "COM PARIDADE";

                # Retira a verificação da compulsória
                $this->verificaCompulsoria = false;

                # Cartilha
                $this->cartilha1 = "lc195compulsoria1.png";
                $this->cartilha2 = "lc195compulsoria2.png";

                # Pontuação
                $this->pontosHomem = 96;
                $this->pontosMulher = 86;

                # Data de Ingresso
                $this->dtIngresso = "31/12/2003";

                # Remuneração
                $this->calculoInicial = "Última remuneração";
                $this->teto = "Remuneração do servidor no cargo efetivo";
                $this->reajuste = "Na mesma data e índice dos servidores ativos";
                $this->paridade = "COM PARIDADE";
                
                # Data da Lei - Só pode aposentar apos essa data
                $this->dataLei = "01/01/2022";

                # Tabela de Pontos
                $this->tabelaM = [
                    [2023, 97],
                    [2024, 97],
                    [2025, 98],
                    [2026, 98],
                    [2027, 99],
                    [2028, 99],
                    [2029, 100],
                    [2030, 100],
                    [2031, 101],
                    [2032, 101],
                    [2033, 102],
                    [2034, 102],
                    [2035, 103],
                    [2036, 103],
                    [2037, 104],
                    [2038, 104],
                    [2039, 105],
                    [2040, 105],
                ];
                $this->tabelaF = [
                    [2023, 87],
                    [2024, 87],
                    [2025, 88],
                    [2026, 88],
                    [2027, 89],
                    [2028, 89],
                    [2029, 90],
                    [2030, 90],
                    [2031, 91],
                    [2032, 91],
                    [2033, 92],
                    [2034, 92],
                    [2035, 93],
                    [2036, 93],
                    [2037, 94],
                    [2038, 94],
                    [2039, 95],
                    [2040, 95],
                    [2041, 96],
                    [2042, 96],
                    [2043, 97],
                    [2044, 97],
                    [2045, 98],
                    [2046, 98],
                    [2047, 99],
                    [2048, 99],
                ];
                break;
            
            ######################################
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

        # Ajusta a data quando for antes da data da Lei
        if (dataMaior($this->dataDireitoAposentadoria, $this->dataLei) == $this->dataLei) {
            $this->dataDireitoAposentadoria = $this->dataLei;
            $this->ajustado = true;
        }

        # Define o texto de retorno    
        if ($this->analiseDtRequesitosCumpridos == "OK" OR $this->dtRequesitosCumpridos == null) {
            if (jaPassou($this->dataDireitoAposentadoria)) {
                $this->textoRetorno = "O Servidor tem direito a esta modalidade de aposentadoria desde:<br/><b>{$this->dataDireitoAposentadoria}</b>";
                $this->textoReduzido = "Desde:<br/><b>{$this->dataDireitoAposentadoria}</b>";
                $this->corFundo = "success";
                $this->temDireito = true;
            } else {
                $this->textoRetorno = "O Servidor terá direito a esta modalidade de aposentadoria em:<br/><b>{$this->dataDireitoAposentadoria}</b>";
                $this->textoReduzido = "Somente em:<br/><b>{$this->dataDireitoAposentadoria}</b>";
                $this->corFundo = "warning";
                $this->temDireito = true;
            }
        } else {
            $this->textoRetorno = "O Servidor <b>Não Tem Direito</b><br/>a essa modalidade de aposentadoria.";
            $this->textoReduzido = "<b>Não Tem Direito</b>";
            $this->corFundo = "alert";
            $this->temDireito = false;
        }

        # Verifica com a data da Aposentadoria Compulsoria
        if ($this->verificaCompulsoria) {
            $dataCompulsoria = $aposentadoria->get_dataAposentadoriaCompulsoria($this->idServidor);

            if ($this->temDireito) {
                if (dataMaior($this->dataDireitoAposentadoria, $dataCompulsoria) == $this->dataDireitoAposentadoria) {
                    $this->textoRetorno = "O Servidor <b>Não Tem Direito</b><br/>a essa modalidade de aposentadoria.<br/>A data em que alcançaria o direito é posterior a data da Aposentadoria Compulsória";
                    $this->textoReduzido = "<b>Não Tem Direito</b>";
                    $this->corFundo = "alert";
                    $this->temDireito = false;
                }
            }
        }
    }

    ###########################################################

    public function exibe_tabelaDados($relatorio = false) {

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
        ];

        # Verifica se tem tempo de contribuição
        if (!is_null($this->contribuicaoHomem)) {
            array_push($array, ["Contribuição", $this->tempoContribuiçãoDescricao, "{$regraContribuicao} anos<br/>(" . ($regraContribuicao * 365) . " dias)", "{$tempoTotal} dias", $this->dataCriterioTempoContribuicao, $this->analiseContribuicao]);
        }

        # Verifica se tem tempo público
        if (!is_null($this->servicoPublico)) {
            array_push($array, ["Serviço Público", $this->tempoPublicoDescicao, "{$this->servicoPublico} anos<br/>(" . ($this->servicoPublico * 365) . " dias)", "{$tempoPublicoIninterrupto} dias", $this->dataCriterioTempoServicoPublico, $this->analisePublico]);
        }

        # Verifica se tem tempo cargo efetivo
        if (!is_null($this->cargoEfetivo)) {
            array_push($array, ["Cargo Efetivo", $this->tempoCargoDescicao, "{$this->cargoEfetivo} anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)", "{$tempoUenf} dias", $this->dataCriterioTempoCargo, $this->analiseCargoEfetivo]);
        }

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

        # Verifica se a data da aposentadoria 
        if ($this->ajustado) {
            $painel = new Callout("warning");
            $painel->abre();
            p("Atenção<br>A data da aposentadoria foi ajustada para {$this->dataLei},"
                    . " pois,<br/>nessa modalidade de aposentadoria, a data não pode ser "
                    . "anterior<br/>a data da Lei Complementar nº 195/2021", "center");
            $painel->fecha();
        }
    }

    ###########################################################

    public function exibe_analise($relatorio = false) {

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

    public function get_dataAposentadoria($idServidor = null) {

        if (!empty($idServidor)) {
            $this->fazAnalise($idServidor);
        }

        return $this->dataDireitoAposentadoria;
    }

    ###########################################################

    public function get_diasFaltantes($idServidor = null) {

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

    public function exibe_tabelaRegras($relatorio = false) {

        $array = [
            ["<p id='pLinha1'>Idade</p><p id='pLinha4'>{$this->idadeDescricao}</p>", $this->idadeMulher . " anos", $this->idadeHomem . " anos"],
        ];

        # Verifica se tem tempo de contribuição
        if (!is_null($this->contribuicaoHomem)) {
            array_push($array, ["<p id='pLinha1'>Contribuição</p><p id='pLinha4'>{$this->tempoContribuiçãoDescricao}</p>", $this->contribuicaoMulher . " anos<br/>(" . ($this->contribuicaoMulher * 365) . " dias)", $this->contribuicaoHomem . " anos<br/>(" . ($this->contribuicaoHomem * 365) . " dias)"]);
        }

        # Verifica se tem tempo público
        if (!is_null($this->servicoPublico)) {
            array_push($array, ["<p id='pLinha1'>Serviço Público</p><p id='pLinha4'>{$this->tempoPublicoDescicao}</p>", $this->servicoPublico . " anos<br/>(" . ($this->servicoPublico * 365) . " dias)", $this->servicoPublico . " anos<br/>(" . ($this->servicoPublico * 365) . " dias)"]);
        }

        # Verifica se tem tempo cargo efetivo
        if (!is_null($this->cargoEfetivo)) {
            array_push($array, ["<p id='pLinha1'>Cargo Efetivo</p><p id='pLinha4'>{$this->tempoCargoDescicao}</p>", $this->cargoEfetivo . " anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)", $this->cargoEfetivo . " anos<br/>(" . ($this->cargoEfetivo * 365) . " dias)"]);
        }

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
        $tabela->set_label(["Requisito", "Mulher", "Homem"]);
        $tabela->set_width([50, 25, 25]);
        $tabela->set_align(["left"]);
        $tabela->set_totalRegistro(false);
        $tabela->set_rodape("");
        $tabela->show();
    }

    ###########################################################

    public function exibe_tabelaRemuneração($relatorio = false) {

        $array = [
            ["Cálculo Inicial", $this->calculoInicial]
        ];

        # Verifica se tem percentual devidoFF
        if (!is_null($this->percentualDevido)) {
            array_push($array, ["Percentual Devido", $this->percentualDevido]);
        }

        # Verifica se tem teto
        if (!is_null($this->teto)) {
            array_push($array, ["Teto", $this->teto]);
        }

        # Coloca o restoF
        array_push($array, ["Reajuste", $this->reajuste]);
        array_push($array, ["Paridade", $this->paridade]);

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
        $tabela->set_label(array("Item", "Descrição"));
        $tabela->set_width(array(30, 70));
        $tabela->set_align(array("left", "left"));
        $tabela->set_totalRegistro(false);
        $tabela->show();
    }

    ###########################################################

    public function exibe_cartilha() {

        $grid2 = new Grid();
        $grid2->abreColuna(12);

        tituloTable("Cartilha");

        $grid2->fechaColuna();
        $grid2->abreColuna(6);

        $figura = new Imagem(PASTA_FIGURAS . $this->cartilha1, null, "100%", "100%");
        $figura->set_id('imgCasa');
        $figura->set_class('imagem');
        $figura->show();

        $grid2->fechaColuna();
        $grid2->abreColuna(6);

        $figura = new Imagem(PASTA_FIGURAS . $this->cartilha2, null, "100%", "100%");
        $figura->set_id('imgCasa');
        $figura->set_class('imagem');
        $figura->show();

        $grid2->fechaColuna();
        $grid2->fechaGrid();
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

    public function exibe_analiseLink($idServidor) {

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

    public function get_textoReduzido($idServidor) {

        # Faz a análise
        $this->fazAnalise($idServidor);

        # Retorna
        return $this->textoReduzido;
    }

    ###########################################################

    public function exibe_telaServidor($idServidor, $idUsuario) {

        # Faz a análise
        $this->fazAnalise($idServidor);

        # Grava no log a atividade
        $intra = new Intra();
        $atividade = "Cadastro do servidor - Aposentadoria - Regras Permanentes<br/>{$this->get_descricao()}";
        $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7, $idServidor);

        $grid1 = new Grid();
        $grid1->abreColuna(12);

        # Exibe a regra
        tituloTable($this->get_descricao(), null, $this->get_legislacao());
        $this->exibe_analise();

        $grid1->fechaColuna();
        $grid1->abreColuna(12, 12, 8);

        $this->exibe_tabelaDados();
        $this->exibe_cartilha();

        $grid1->fechaColuna();
        $grid1->abreColuna(12, 12, 4);

        $this->exibe_tabelaRemuneração();
        $this->exibe_tabelaRegras();

        $grid1->fechaColuna();
        $grid1->fechaGrid();
    }

    ###########################################################

    public function exibe_relatorio($idServidor, $idUsuario) {
        # Faz a análise
        $this->fazAnalise($idServidor);

        # Grava no log a atividade
        $intra = new Intra();
        $atividade = "Visualizou o relatório de Aposentadoria - Regras Permanentes<br/>{$this->get_descricao()}";
        $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 4, $idServidor);

        # Dados do Servidor
        Grh::listaDadosServidorRelatorio2(
                $idServidor,
                $this->get_descricao(),
                $this->get_legislacao() . "<br/>" . $this->get_tipo(),
                true,
                $this->mensagemRelatorio
        );
        br();

        # Exibe a regra
        p($this->exibe_analise(true), "center");
        $this->exibe_tabelaDados(true);
        $this->exibe_tabelaRegras(true);
        $this->exibe_tabelaRemuneração(true);
    }

    ###########################################################
}
