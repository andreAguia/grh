<?php
class Ci{
 /**
  * Monta uma Ci
  * 
  * @author André Águia (Alat) - alataguia@gmail.com
  * 
  * @var private $projeto        integer NULL O id do projeto a ser acessado
  * 
  */
    
    private $ciNumero = NULL;
    private $ciData = NULL;
    private $ciAssunto = NULL;
    private $ciTexto = NULL;
    
    private $origemNome = NULL;
    private $origemSetor = NULL;
    private $origemDescricao = NULL;
    private $origemIdFuncional = NULL;
    
    private $destinoNome = NULL;
    private $destinoSetor = NULL;
    
    private $rodapeNome = "Gerência de Recursos Humanos - GRH";
    private $rodapeEndereco = "Av. Alberto Lamego, 2000 – Prédio E-1  - Sala 217 -  CEP 28.013-602 -  Campos dos Goytacazes - RJ";
    private $rodapeTelefone = "(22) 2739-7064";
    
    ###########################################################
    
    public function __construct(){
    /**
     * Inicia a Ci e preenche oas variáveis com valores padrão
     */
        
    	# Gerente do GRH (id 66)
        $pessoal = new Pessoal();
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
    
    public function __call ($metodo, $parametros){
        ## Se for set, atribui um valor para a propriedade
        if (substr($metodo, 0, 3) == 'set'){
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
    
    private function rodape(){
        br(3);
        hr();
        p('<b>'.$this->rodapeNome.'</b><br/>'.$this->rodapeEndereco.'<br/>Telefone: '.$this->rodapeTelefone,'pCiRodape');
    }
    
    ###########################################################
    
    public function show(){
    /**
     * Exibe a Ci
     * 
     * @syntax $ci->show();
     */
    
        ## Monta o Relatório 
        # Menu
        $menuRelatorio = new menuRelatorio();
        $menuRelatorio->set_botaoVoltar(NULL);    
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
        p('CI '.$this->origemSetor.' Nº '.$this->ciNumero,'pCiNum');

        $grid->fechaColuna();
        $grid->abreColuna(7);

        # Data
        p('Campos dos Goytacazes, '.dataExtenso($this->ciData),'pCiData');

        $grid->fechaColuna();
        $grid->fechaGrid();

        # Origem
        p('De:&nbsp&nbsp&nbsp&nbsp'.$this->origemNome.'<br/>'
        . '&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp'.$this->origemDescricao,'pCi');
        br();

        # Destino
        p('Para:&nbsp&nbsp'.$this->destinoNome.'<br/>'
        . '&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp'.$this->destinoSetor,'pCi');
        br();

        # Assunto
        p("Assunto: ".$this->ciAssunto,'pCi');
        br();

        # Texto
        p('&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp'.$this->ciTexto,'pCi');
        br();

        # Atenciosamente
        p('Atenciosamente','pCi');
        br(5);

        # Assinatura
        #p('____________________________________________________','pCiAssinatura');
        p($this->origemNome.'<br/>'.$this->origemSetor.'<br/>Id Funcional n° '.$this->origemIdFuncional,'pCiAssinatura');

        $this->rodape();
        
        $grid->fechaColuna();
        $grid->fechaGrid();
    }
}