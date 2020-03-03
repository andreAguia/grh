<?php
class Carta{
 /**
  * Monta uma Ci
  * 
  * @author André Águia (Alat) - alataguia@gmail.com
  * 
  * @var private $projeto        integer NULL O id do projeto a ser acessado
  * 
  */
    
    private $data = NULL;
    private $nomeCarta = NULL;
    private $texto = NULL;
    
    private $origemNome = NULL;
    private $origemSetor = NULL;
    private $origemDescricao = NULL;
    private $origemIdFuncional = NULL;
    
    private $destinoNome = NULL;
    private $destinoSetor = NULL;
    
    private $rodapeNome = "Gerência de Recursos Humanos - GRH";
    private $rodapeEndereco = "Av. Alberto Lamego, 2000 – Prédio E-1  - Sala 217 -  CEP 28.013-602 -  Campos dos Goytacazes - RJ";
    private $rodapeTelefone = "(22) 2739-7064";
    
    private $saltoAssinatura = 3;
    
    ###########################################################
    
    public function __construct(){
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
	    
    public function set_texto($texto){
    /**
     * Inclui um objeto Input ao formulário
     * 
     * @syntax $form->add_item($objeto);
     * 
     * @param $controle object NULL Objeto Input a ser inserido no Formulário
     * 
     */
    
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
        $div = new Div('rodape');
        $div->abre();

            hr();
            p('<b>'.$this->rodapeNome.'</b><br/>'.$this->rodapeEndereco.'<br/>Telefone: '.$this->rodapeTelefone,'pCiRodape');
            
        $div ->fecha();
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
        

        # Limita o tamanho da tela
        $grid = new Grid("center");
        $grid->abreColuna(11);
        
        if(!vazio($this->nomeCarta)){
            br(2);
            p($this->nomeCarta,'pDeclaracaoTitulo');
            br(2);
        }
        
        # Data
        if(vazio($this->data)){
            $this->data = date("d/m/Y");
        }

        $grid = new Grid();
        $grid->abreColuna(5);

        $grid->fechaColuna();
        $grid->abreColuna(7);

        # Data
        p('Campos dos Goytacazes, '.dataExtenso($this->data),'pCiData');
        br();
        
        $grid->fechaColuna();
        $grid->fechaGrid();
        br();
        
        # Origem
        p('Ilmo(a) Sr(a)','pCiDePara1');
        p($this->destinoNome,'pCiDePara1');
        p($this->destinoSetor,'pCiDePara1');
        br();
        
        # Prezado
        p("Prezado(a) Senhor(a),",'pCiNum');
        
        # Texto
        foreach($this->texto as $textoCi){
            p($textoCi,'pCi');
        }
        br();

        # Atenciosamente
        p('Atenciosamente,','pCiNum');
        br(4);

        # Assinatura
        #p('____________________________________________________','pCiAssinatura');
        p($this->origemNome.'<br/>'.$this->origemDescricao.'<br/>Id Funcional n° '.$this->origemIdFuncional,'pCiAssinatura');
        
        $grid->fechaColuna();
        $grid->fechaGrid();
        
        $this->rodape();
    }
}