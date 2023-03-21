<?php

class Declaracao {

    /**
     * Monta uma Ci
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     *
     * @var private $projeto        integer null O id do projeto a ser acessado
     *
     */
    private $data = null;
    private $texto = null;
    private $origemNome = null;
    private $origemSetor = null;
    private $origemDescricao = null;
    private $origemIdFuncional = null;
    private $rodapeNome = "Gerência de Recursos Humanos - GRH";
    private $rodapeEndereco = "Av. Alberto Lamego, 2000 – Prédio E-1  - Sala 217 -  CEP 28.013-602 -  Campos dos Goytacazes - RJ";
    private $rodapeTelefone = "(22) 2739-7064";
    private $saltoRodape = 3;
    private $saltoAssinatura = 3;
    private $aviso = null;
    private $carimboCnpj = false;
    private $rodapeSoUntimaPag = false;
    private $assinatura = false;
    private $declaracaoNome = "DECLARAÇÃO";
    
    private $formCampos = null;    // array com campos para o formulario
    private $formFocus = null;     // Campos a receber foco no form
    private $formLink = null;    // para onde vai o post

    ###########################################################

    public function __construct() {
        /**
         * Inicia a Ci e preenche oas variáveis com valores padrão
         */
        # Conecta ao banco de dados
        $pessoal = new Pessoal();

        # Gerente do GRH (id 66)
        $idGerenteGrh = $pessoal->get_gerente(66);
        $nomeGerente = $pessoal->get_nome($idGerenteGrh);
        $idFuncionalGerente = $pessoal->get_idFuncional($idGerenteGrh);
        $descricao = $pessoal->get_cargoComissaoDescricao($idGerenteGrh);

        # Valores padrão de Origem
        $this->origemNome = $nomeGerente;
        $this->origemSetor = "UENF/DGA/GRH";
        $this->origemIdFuncional = $idFuncionalGerente;
        $this->origemDescricao = $descricao;
    }

    ###########################################################

    public function set_texto($texto, $salto = true) {
        /**
         * Inclui um objeto Input ao formulário
         *
         * @syntax $form->add_item($objeto);
         *
         * @param $controle object null Objeto Input a ser inserido no Formulário
         *
         */
        $this->texto[] = [$texto, $salto];
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
    public function __call($metodo, $parametros) {
        ## Se for set, atribui um valor para a propriedade
        if (substr($metodo, 0, 3) == 'set') {
            $var = substr($metodo, 4);
            $this->$var = $parametros[0];
        }

        # Se for Get, retorna o valor da propriedade
        #if (substr($metodo, 0, 3) == 'get')
        #{
        # $var = substr($metodo, 4);
        #  return $this->$var;
        #}
    }

    ###########################################################

    /**
     * Método rodape
     *
     * Exibe o rodapé
     */
    private function rodape() {

        if ($this->rodapeSoUntimaPag) {
            br($this->saltoRodape);
            hr();
            p("<b>{$this->rodapeNome}</b><br/>{$this->rodapeEndereco}<br/>Telefone: {$this->rodapeTelefone}", "pCiRodape");
        } else {
            $div = new Div('rodape');
            $div->abre();

            hr();
            p("<b>{$this->rodapeNome}</b><br/>{$this->rodapeEndereco}<br/>Telefone: {$this->rodapeTelefone}", "pCiRodape");

            $div->fecha();
        }
    }

    ###########################################################

    public function show() {
        /**
         * Exibe a Ci
         *
         * @syntax $ci->show();
         */
        ## Monta o Relatório
        # Menu
        $menuRelatorio = new menuRelatorio();
        $menuRelatorio->set_botaoVoltar(null);
        $menuRelatorio->set_formCampos($this->formCampos);
        $menuRelatorio->set_formFocus($this->formFocus);
        $menuRelatorio->set_formLink($this->formLink);
        $menuRelatorio->set_aviso($this->aviso);
        $menuRelatorio->show();

        # Cabeçalho do Relatório (com o logotipo)
        $relatorio = new Relatorio();
        $relatorio->exibeCabecalho();

        #hr();
        # Limita o tamanho da tela
        $grid = new Grid("center");
        $grid->abreColuna(11);
        br();

        # Declaração
        p($this->declaracaoNome, 'pDeclaracaoTitulo');
        br();

        # Texto
        foreach ($this->texto as $textoDec) {
            if ($textoDec[1]) {
                p($textoDec[0], 'pCi');    // com a identação
            } else {
                p($textoDec[0], 'pCiNum'); // sem a identação
            }
        }

        if ($this->carimboCnpj) {
            $grid = new Grid();
            $grid->abreColuna(8);

            # Data
            br(2);
            if (empty($this->data)) {
                p('Campos dos Goytacazes, ______ de ____________________ de _______', 'pDeclaracaoData');
            } else {
                p('Campos dos Goytacazes, ' . dataExtenso($this->data), 'pDeclaracaoData');
            }

            $grid->fechaColuna();
            $grid->abreColuna(4);

            $figura = new Imagem(PASTA_FIGURAS . 'carimboCnpj.png', null, 200, 120);
            $figura->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
        } else {
            # Data
            br(2);
            if (empty($this->data)) {
                p('Campos dos Goytacazes, ______ de ____________________ de _______', 'pDeclaracaoData');
            } else {
                p('Campos dos Goytacazes, ' . dataExtenso($this->data), 'pDeclaracaoData');
            }
        }
        br($this->saltoAssinatura);

        # Assinatura
        if ($this->assinatura) {
            $grid = new Grid("center");
            $grid->abreColuna(4);

            $figura = new Imagem(PASTA_FIGURAS . 'assinatura.png', null, 150, 150);
            $figura->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
        }

        $textoAssinatura = "{$this->origemNome}<br/>";

        if (!empty($this->origemDescricao)) {
            $textoAssinatura .= "{$this->origemDescricao}<br/>";
        }

        if (!empty($this->origemIdFuncional)) {
            $textoAssinatura .= "Id Funcional n° {$this->origemIdFuncional}<br/>";
        }

        p($textoAssinatura, 'pCiAssinatura');

        $grid->fechaColuna();
        $grid->fechaGrid();

        $this->rodape();
    }
}
