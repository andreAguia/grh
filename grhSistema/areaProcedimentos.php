<?php
/**
 * Gestão de Projetos
 *  
 * By Alat
 */

# Servidor logado 
$idUsuario = NULL;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,1);

if($acesso){
    
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Verifica a fase do programa
    $fase = get('fase','inicial');
    
    # Pega os ids quando se é necessário de acordo com a fase
    $idProcedimento = get('idProcedimento',get_session('idProcedimento'));
    
    # Passa para Session o que veio do get
    set_session('idProcedimento',$idProcedimento);
    
    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();
    
    # Limita o tamanho da tela
    $grid = new Grid();
    $grid->abreColuna(12);
    
    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Menu
    botaoVoltar("grh.php");
    
    # Título
    titulo("Procedimentos");
    br();
    
    # Define o grid
    $col1P = 6;
    $col1M = 4;
    $col1L = 3;

    $col2P = 12 - $col1P;
    $col2M = 12 - $col1M;
    $col2L = 12 - $col1L;
    
    # Limita o tamanho da tela
    $grid = new Grid();
    $grid->abreColuna($col1P,$col1M,$col1L);
    
    # Menu de Cadernos
    Procedimento::menu($idProcedimento);
    
    $grid->fechaColuna();
    
    switch ($fase){ 
        
#############################################################################################################################
#   Inicial
#############################################################################################################################
        
        case "inicial" :
            $grid->abreColuna($col2P,$col2M,$col2L);
            
            $painel = new Callout();
            $painel->abre();
                            
            br(5);
            p("Manual de Procedimentos","f20","center");
            p("Versão 0.1","f16","center");
            p("Autor: André Águia","f14","center");
            br(5);

            $painel->fecha(); 
            
            $grid->fechaColuna();
            $grid->fechaGrid(); 
            break;
                 
#############################################################################################################################
#   Caderno
#############################################################################################################################
        
        case "menu" :
                         
            # Area das notas
            $grid->abreColuna($col2P,$col2M,$col2L);
            
            $painel = new Callout();
            $painel->abre();
            
            # Menu
            $div = new Div('divEditaNota2');
            $div->abre();
            
            $menu1 = new MenuBar("small button-group");

            # Novo Caderno
            $link = new Link("Novo",'?fase=cadernoNovo');
            $link->set_class('button secondary');
            $link->set_title('Novo Caderno');
            $menu1->add_link($link,"right");

            $menu1->show();
            $div->fecha();
            
            # Pega os projetos cadastrados
            $select = 'SELECT idCaderno,
                              caderno,
                              descricao
                         FROM tbprojetocaderno
                      ORDER BY numOrdem, caderno';

            $dadosCaderno = $intra->select($select);
            $numCadernos = $intra->count($select);
            
            # Caderno
            p('Cadernos','descricaoProjetoTitulo');
            hr("projetosTarefas");
            br();
        
            # Inicia o menu
            $menu1 = new Menu();
            
            # Verifica se tem cadernos
            if($numCadernos > 0){
                
                # Percorre o array 
                foreach ($dadosCaderno as $valor){
                    $numNotas = $projeto->get_numeroNotas($valor[0]);
                    $texto = $valor[1]." <span id='numProjeto'>$numNotas</span>";                

                    $menu1->add_item('titulo2',"<i class='fi-book'></i> ".$texto,'?fase=caderno&idCaderno='.$valor[0],"Caderno: ".$valor[1]);                    
                }           

            }
            $menu1->show();
            $painel->fecha();  
            
            $grid->fechaColuna();
            $grid->fechaGrid();   
            break;
            
    ###########################################################        
            
        case "novoProcedimento" :
        case "editaProcedimento" :    
             
            $grid->abreColuna($col2P,$col2M,$col2L);
             
            # Verifica se é incluir ou editar
            if(!is_null($idProcedimento)){
                # Pega os dados dessa nota
                $dados = $projeto->get_dadosProcedimento($idProcedimento);
                $titulo = "Editar Nota";
            }else{
                $dados = array(NULL,NULL,NULL,NULL,NULL,NULL);
                $titulo = "Nova Nota";
            } 
             
            # Titulo
            $grid = new Grid();
            $grid->abreColuna(12);
                p($titulo,"f18");
            $grid->fechaColuna(); 
            $grid->fechaGrid(); 
            hr("projetosTarefas");
            
            # Pega os dados da combo categoria
            $select = 'SELECT categoria,
                              categoria
                         FROM tbprocedimento
                     ORDER BY categoria';
            
            $datalistCategoria = $pessoal->select($select);
            
            # Formuário
            $form = new Form('?fase=validaNota');        
                    
            # Título
            $controle = new Input('titulo','texto','Título:',1);
            $controle->set_size(100);
            $controle->set_linha(1);
            $controle->set_col(5);
            $controle->set_required(TRUE);
            $controle->set_autofocus(TRUE);
            $controle->set_title('Título da nota');
            $controle->set_valor($dados[2]);
            $form->add_item($controle);
            
            # Categoria
            $controle = new Input('categoria','texto','Categoria:',1);
            $controle->set_size(20);
            $controle->set_linha(1);
            $controle->set_col(5);
            $controle->set_datalist($datalistCategoria);
            if(!is_null($idProcedimento)){
                $controle->set_valor($dados[1]);
            }
            $form->add_item($controle);
            
            # numOrdem
            $controle = new Input('numOrdem','texto','Ordem:',1);
            $controle->set_size(5);
            $controle->set_linha(1);
            $controle->set_col(2);
            $controle->set_title('Ordem da nota na lista');
            $controle->set_valor($dados[4]);
            $form->add_item($controle);
            
            # descricao            
            $controle = new Input('descricao','textarea','Descrição:',1);
            $controle->set_size(array(80,2));
            $controle->set_linha(2);
            $controle->set_col(12);
            $controle->set_title('Breve Descrição da nota');
            $controle->set_valor($dados[5]);
            $form->add_item($controle);
                                    
            # nota            
            $controle = new Input('nota','editor','Nota:',1);
            $controle->set_size(array(80,15));
            $controle->set_linha(2);
            $controle->set_col(12);
            $controle->set_title('Corpo da nota');
            $controle->set_valor($dados[3]);
            $form->add_item($controle);
            
            # submit
            $controle = new Input('submit','submit');
            $controle->set_valor('Salvar');
            $controle->set_linha(3);
            $form->add_item($controle);
            
            $form->show();
            
            $grid->fechaColuna();
            $grid->fechaGrid();    
            break;
                        
        ###########################################################
            
        case "validaNota" :
            
            # Recuperando os valores
            $titulo = post('titulo');
            $caderno = post('idCaderno');
            $nota = post('nota');
            $numOrdem = post('numOrdem');
            $descricao = post('descricao');
                      
            # Cria arrays para gravação
            $arrayNome = array("titulo","idCaderno","nota","numOrdem","descricao");
            $arrayValores = array($titulo,$caderno,$nota,$numOrdem,$descricao);
            
            # Grava	
            $intra->gravar($arrayNome,$arrayValores,$idNota,"tbprojetonota","idNota");
            
            # Pega o id quando for inclusão
            if(is_null($idNota)){
                $idnota = $intra->get_lastId();
                set_session('idNota',$idnota);
            }
            
            loadPage("?fase=caderno");
            break;
                        
        ###########################################################
    }    
        
    $grid->fechaColuna();
    $grid->fechaGrid();  
    
    $page->terminaPagina();
}else{
    loadPage("login.php");
}