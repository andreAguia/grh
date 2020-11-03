<?php

class ReciboRpa
{

    /**
     * Monta um recibo RPA
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     *
     * @var private $projeto        integer null O id do projeto a ser acessado
     *
     */
    private $texto = null;
    private $aviso = null;
    private $idRecibo = null;
    private $reciboNome = "RECIBO DE PAGAMENTO A AUTÔNOMO - RPA";

    ###########################################################

    public function __construct($idRecibo = null)
    {
        /**
         * Inicia O Recibo
         */
        $this->idRecibo = $idRecibo;
    }

    ###########################################################

    public function set_texto($texto)
    {
        $this->texto[] = $texto;
    }

    ###########################################################

    /**
     * Métodos get e set construídos de forma automática pelo
     * metodo mágico __call.
     * Esse método cria um set e um get para todas as propriedades da classe.
     * Um método existente tem prioridade sobre os métodos criados pelo __call.
     *
     * O formato dos métodos devem ser:
     *     set_propriedade
     *     get_propriedade
     *
     * @param     $metodo        O nome do metodo
     * @param     $parametros    Os parâmetros inseridos
     */
    public function __call($metodo, $parametros)
    {
        ## Se for set, atribui um valor para a propriedade
        if (substr($metodo, 0, 3) == 'set') {
            $var = substr($metodo, 4);
            $this->$var = $parametros[0];
        }
    }

    ###########################################################

    private function montaRecibo()
    {
        # Pega os dados da RPA
        $rpa = new Rpa();
        $dados = $rpa->get_dados($this->idRecibo);
        $idPrestador = $dados["idPrestador"];

        # Pega os dados dp Prestador
        $rpaPrestador = new RpaPrestador();
        $dadosPrestador = $rpaPrestador->get_dados($idPrestador);

        # Pega o valor do INSS
        $inss = new RpaInss();
        $valorInss = $inss->getValor($this->idRecibo);

        # Pega o valor do IR
        $ir = new RpaIr();
        $valorIr = $ir->getValor($this->idRecibo);

        $valorTotal = ($dados["valor"] - $valorInss[0] - $valorIr[0]);
        $valorTotalFormatado = formataMoeda2($dados["valor"] - $valorInss[0] - $valorIr[0]);

        # Pega os dados da universidade
        $intra = new Intra();
        $universidadeNome = $intra->get_variavel("universidadeNome");
        $universidadeCnpj = $intra->get_variavel("universidadeCnpj");
        $universidadeEndereco = $intra->get_variavel("universidadeEndereco");

        p($this->reciboNome, 'pRpaTitulo');

        /*
         *  Nome da Uiniversidade
         */
        $div = new Div("divRpaNomeUniversidade");
        $div->abre();

        p("TOMADOR DE SERVIÇOS", "pRpaLabel");
        hr("rpa");
        p($universidadeNome, "pRpaDados");

        $div->fecha();

        # CNPJ
        $div = new Div("divRpaCnpjUniversidade");
        $div->abre();

        p("CNPJ", "pRpaLabel");
        hr("rpa");
        p($universidadeCnpj, "pRpaDadosCentro");

        $div->fecha();

        /*
         *  Número do Recibo
         */
        $div = new Div("divRpaNumeroRecibo");
        $div->abre();

        p("RECIBO NÚMERO", "pRpaLabel");
        hr("rpa");
        p(str_pad($dados['idRecibo'], 4, '0', STR_PAD_LEFT), "pRpaDadosCentro");

        $div->fecha();


        # Texto
        $div = new Div("divRpaTexto");
        $div->abre();

        # Texto
        $textoRecibo = "Recebi da empresa acima identificada, pela prestação dos serviços de"
                . " {$dados['servico']}, a importância de {$valorTotalFormatado} (" . moedaExtenso($valorTotal) . ")"
                . " conforme discriminativo abaixo:";

        p($textoRecibo, 'pRpa');

        $div->fecha();

        /*
         *  Dados contribuinte
         */
        $div = new Div("divRpaDadosContribuinte");
        $div->abre();

        p("DADOS do CONTRIBUINTE INDIVIDUAL", "pRpaLabel");
        hr("rpa");

        $valores = [
            ["Nome:", $dadosPrestador["prestador"]],
            ["Endereço:", "{$dadosPrestador["endereco"]} - {$dadosPrestador["bairro"]}"],
            ["Cidade:", "{$dadosPrestador["cidade"]} - {$dadosPrestador["estado"]}"],
            ["Cep:", "{$dadosPrestador["cep"]}"],
            ["CPF:", "{$dadosPrestador["cpf"]}"],
            ["RG:", "{$dadosPrestador["identidade"]}"]
        ];

        $tabela = new Tabela(null, "tabelaRpa");
        $tabela->set_conteudo($valores);
        $tabela->set_label([null, null]);
        $tabela->set_align(["left", "left"]);
        $tabela->set_totalRegistro(false);
        $tabela->show();

        $div->fecha();

        /*
         *  Demonstrativo dos valores
         */
        $div = new Div("divRpaDemonstrativo");
        $div->abre();

        p("DEMONSTRATIVO", "pRpaLabel");
        hr("rpa");

        $valores = [
            ["Serviço Prestado", formataMoeda2($dados["valor"])],
            ["Descontos", null],
            ["INSS", "(" . formataMoeda2($valorInss[0]) . ")"],
            ["IRRS", "(" . formataMoeda2($valorIr[0]) . ")"],
            ["Total Descontos", "(" . formataMoeda2($valorInss[0] + $valorIr[0]) . ")"]
        ];

        $tabela = new Tabela(null, "tabelaRpa");
        $tabela->set_conteudo($valores);
        $tabela->set_label([null, null]);
        $tabela->set_align(["left", "right"]);
        $tabela->set_totalRegistro(false);
        $tabela->show();

        hr("rpa");

        $valores = [
            ["Valor Líquido", $valorTotalFormatado]
        ];

        $tabela = new Tabela(null, "tabelaRpa");
        $tabela->set_conteudo($valores);
        $tabela->set_label([null, null]);
        $tabela->set_align(["left", "right"]);
        $tabela->set_totalRegistro(false);
        $tabela->show();

        $div->fecha();

        /*
         * INSS
         */

        $div = new Div("divRpaDadosContribuinte");
        $div->abre();

        p("INSS<br>Valor a ser informado na GPIF e recolhido por GPS pelo tomador de serviço", "pRpaLabel");
        hr("rpa");

        $valores = [
            [formataMoeda2($dados["valor"]), $valorInss[1] . "%", formataMoeda2($valorInss[0])]
        ];

        $tabela = new Tabela(null, "tabelaRpa");
        $tabela->set_conteudo($valores);
        $tabela->set_label(["Salário de Contribuição", "Aliquota", "Valor"]);
        $tabela->set_align(["center"]);
        $tabela->set_totalRegistro(false);
        $tabela->show();

        $div->fecha();

        $div = new Div("divRpaDadosContribuinte");
        $div->abre();

        p("INSS<br>Salário de contribuição acumulado pelo prestador de serviços", "pRpaLabel");
        hr("rpa");

        $valores = [
            [formataMoeda2($dados["valor"]), formataMoeda2($valorInss[0])]
        ];

        $tabela = new Tabela(null, "tabelaRpa");
        $tabela->set_conteudo($valores);
        $tabela->set_label(["Salário de Contribuição", "Valor"]);
        $tabela->set_align(["center"]);
        $tabela->set_totalRegistro(false);
        $tabela->show();

        $div->fecha();

        /*
         * IRRS
         */

        $div = new Div("divRpaDadosContribuinte");
        $div->abre();

        p("IRRS<br>Valor a ser recolhido por DARF pelo tomador de serviço", "pRpaLabel");
        hr("rpa");

        $valores = [
            [formataMoeda2($dados["valor"]), formataMoeda2($valorIr[2]), formataMoeda2($valorIr[0])]
        ];

        $tabela = new Tabela(null, "tabelaRpa");
        $tabela->set_conteudo($valores);
        $tabela->set_label(["Base de Cálculo", "Deduções", "Valor"]);
        $tabela->set_align(["center"]);
        $tabela->set_totalRegistro(false);
        $tabela->show();

        $div->fecha();

        /*
         * Assinatura
         */

        $div = new Div("divRpaAssinatura");
        $div->abre();

        $valores = [
            ["Campos dos Goytacazes, " . dataExtenso(date_to_php($dados["dtPgto"]))]
        ];

        $tabela = new Tabela(null, "tabelaRpa");
        $tabela->set_conteudo($valores);
        $tabela->set_label([null]);
        $tabela->set_align(["left"]);
        $tabela->set_totalRegistro(false);
        $tabela->show();

        br(2);
        hr("rpa");
        p($dadosPrestador["prestador"], "pRpaLabel");

        $div->fecha();
    }

    ###########################################################

    public function show()
    {
        /**
         * Exibe O Recibo
         *
         * @syntax $ci->show();
         */
        # Menu
        $menuRelatorio = new menuRelatorio();
        $menuRelatorio->set_botaoVoltar(null);
        $menuRelatorio->set_aviso($this->aviso);
        $menuRelatorio->show();

        # Limita o tamanho da tela
        $grid = new Grid("center");
        $grid->abreColuna(12);

        # Monta o recibo
        $this->montaRecibo();

        hr("picote");

        # Monta a cópia do recibo
        $this->montaRecibo();

        $grid->fechaColuna();
        $grid->fechaGrid();
    }

}
