<?php

class Ci {

    /**
     * Monta uma Ci
     * 
     * @author André Águia (Alat) - alataguia@gmail.com
     * 
     * @var private $projeto        integer null O id do projeto a ser acessado
     * 
     */
    private $numero = null;
    private $data = null;
    private $assunto = null;
    private $texto = null;
    private $origemNome = null;
    private $origemSetor = null;
    private $origemDescricao = null;
    private $origemIdFuncional = null;
    private $destinoNome = null;
    private $destinoSetor = null;
    private $rodapeNome = "Gerência de Recursos Humanos - GRH";
    private $rodapeEndereco = "Av. Alberto Lamego, 2000 – Prédio E-1  - Sala 217 -  CEP 28.013-602 -  Campos dos Goytacazes - RJ";
    private $rodapeTelefone = "(22) 2739-7064";
    private $saltoRodape = 3;

    ###########################################################

    public function __construct($numero, $data, $assunto) {
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

        # Valores informados
        $this->numero = $numero;
        $this->data = $data;
        $this->assunto = $assunto;
    }

    ###########################################################

    public function set_texto($texto) {
        /**
         * Inclui um objeto Input ao formulário
         * 
         * @syntax $form->add_item($objeto);
         * 
         * @param $controle object null Objeto Input a ser inserido no Formulário
         * 
         */
        $this->texto[] = $texto;
    }

    ###########################################################

    public function set_nomeAssinatura($origemNome, $origemDescricao,$origemIdFuncional){
        
        /**
         * Altera o servidor da assinatura no lugar do gerente
         * 
         * @syntax $form->set_nomeAssinatura($origemNome, $origemDescricao, $origemIdFuncional)
         * 
         * @param $origemNome texto null O nome do servidor
         * @param $origemDescricao texto null Descrição do cargo / Função desse servidor
         * @param $origemIdFuncional texto null o idFuncional desse servidor
         * 
         */
        $this->origemNome = $origemNome;
        $this->origemDescricao = $origemDescricao;
        $this->origemIdFuncional = $origemIdFuncional;
    }

    ###########################################################

    /**
     * Métodos get e set construídos de forma automática pelo 
     * metodo mágico __call.
     * Esse método cria um set e um get para todas as propriedades da classe.
     * Um método existente tem prioridade sobre os métodos criados pelo __call.
     * 
     * O formato dos métodos devem ser:
     * 	set_propriedade
     * 	get_propriedade
     * 
     * @param 	$metodo		O nome do metodo
     * @param 	$parametros	Os parâmetros inseridos  
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
        $div = new Div('rodape');
        $div->abre();

        hr();
        p('<b>' . $this->rodapeNome . '</b><br/>' . $this->rodapeEndereco . '<br/>Telefone: ' . $this->rodapeTelefone, 'pCiRodape');

        $div->fecha();
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
        $menuRelatorio->show();

        # Cabeçalho do Relatório (com o logotipo)
        $relatorio = new Relatorio();
        $relatorio->exibeCabecalho();

        hr();

        # Limita o tamanho da tela
        $grid = new Grid("center");
        $grid->abreColuna(11);

        $grid = new Grid();
        $grid->abreColuna(5);

        # CI
        p('CI ' . $this->origemSetor . ' Nº ' . $this->numero, 'pCiNum');

        $grid->fechaColuna();
        $grid->abreColuna(7);

        # Data
        p('Campos dos Goytacazes, ' . dataExtenso($this->data), 'pCiData');

        $grid->fechaColuna();
        $grid->fechaGrid();
        br();

        # Origem
        p('De: &nbsp&nbsp' . $this->origemNome, 'pCiDePara1');
        p($this->origemDescricao, 'pCiDePara2');
        br();

        # Destino
        p('Para: ' . $this->destinoNome, 'pCiDePara1');
        p($this->destinoSetor, 'pCiDePara2');
        br();

        # Assunto
        p("Assunto: " . $this->assunto, 'pCiNum');
        br();

        # Prezado
        p("Prezado(a) Senhor(a)", 'pCiNum');

        # Texto
        foreach ($this->texto as $textoCi) {
            p($textoCi, 'pCi');
        }
        br();

        # Atenciosamente
        p('Atenciosamente,', 'pCiNum');
        br(2);

        # Assinatura
        #p('____________________________________________________','pCiAssinatura');
        p($this->origemNome . '<br/>' . $this->origemDescricao . '<br/>Id Funcional n° ' . $this->origemIdFuncional, 'pCiAssinatura');

        $grid->fechaColuna();
        $grid->fechaGrid();

        $this->rodape();
    }

}
