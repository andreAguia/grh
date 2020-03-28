<?php
/**
 * Cadastro de Classes e Padrões (Tabela Salarial)
 *  
 * By Alat
 */

# Reservado para o servidor logado
$idUsuario = NULL;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso){    
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase','listar');
    
    # Verifica se veio da rotina de PCV
    $pcv = get('pcv');
    
    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh',FALSE);
    if($grh){
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de tabela salarial";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    
    if (is_null(post('parametroPlano'))){					# Se o parametro n?o vier por post (for nulo)
        $parametroPlano = retiraAspas(get_session('sessionParametroPlano'));	# passa o parametro da session para a variavel parametro retirando as aspas
    }else{ 
        $parametroPlano = post('parametroPlano');                # Se vier por post, retira as aspas e passa para a variavel parametro
        set_session('sessionParametroPlano',$parametroPlano);    # transfere para a session para poder recuperá-lo depois
    }
    
    if (is_null(post('parametroNivel'))){					# Se o parametro n?o vier por post (for nulo)
        $parametroNivel = retiraAspas(get_session('sessionParametroNivel'));	# passa o parametro da session para a variavel parametro retirando as aspas
    }else{ 
        $parametroNivel = post('parametroNivel');                # Se vier por post, retira as aspas e passa para a variavel parametro
        set_session('sessionParametroNivel',$parametroNivel);    # transfere para a session para poder recuperá-lo depois
    }

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Tabela Salarial');

    # bot?o de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # select da lista
    $objeto->set_selectLista ('SELECT idClasse,
                                      tbplano.numDecreto,
                                      tbclasse.nivel,
                                      tbtipocargo.cargo,
                                      faixa,
                                      valor,                 
                                      CASE tbplano.planoAtual                                        
                                           WHEN 1 THEN "Vigente"
                                           ELSE "Antigo"
                                      end,                      
                                      idClasse
                                 FROM tbclasse JOIN tbplano USING (idPlano)
                                               LEFT JOIN tbtipocargo USING (idTipoCargo)
                                WHERE tbclasse.nivel LIKE "%'.$parametroNivel.'%"
                                  AND tbplano.idPlano LIKE "%'.$parametroPlano.'%"   
                             ORDER BY tbplano.planoAtual desc,tbplano.dtPublicacao desc, tbclasse.nivel desc, faixa asc');

    # select do edita
    $objeto->set_selectEdita('SELECT nivel,
                                     idTipoCargo,
                                     faixa,
                                     valor,
                                     idPlano
                                FROM tbclasse
                               WHERE idClasse = '.$id);
    
    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');
    
    # Dá acesso a exclusão somente ao administrador
    if(Verifica::acesso($idUsuario,1)){
        $objeto->set_linkExcluir('?fase=excluir');
    }

    # Parametros da tabela
    $objeto->set_label(array("id","Plano","Nível","Cargo","Faixa","Valor","Status"));
    #$objeto->set_width(array(5,30,20,10,10,10));
    $objeto->set_align(array("center"));
    $objeto->set_funcao(array(NULL,NULL,NULL,NULL,NULL,"formataMoeda"));

    $planoAtual = $pessoal->get_numDecretoPlanoAtual();

    $objeto->set_formatacaoCondicional(array(array('coluna' => 6,
                                                   'valor' => "Vigente",
                                                   'operador' => '<>',
                                                   'id' => 'inativo')));
    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbclasse');

    # Nome do campo id
    $objeto->set_idCampo('idClasse');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);
    
    # Pega os dados da combo de cargo
    $cargo = $pessoal->select('SELECT idTipoCArgo, 
                                       cargo
                                  FROM tbtipocargo
                              ORDER BY cargo desc');
    array_unshift($cargo, array(0,NULL)); 

    # Pega os dados da combo de Plano e Cargos
    $result = $pessoal->select('SELECT idPlano, 
                                       numDecreto
                                  FROM tbplano
                              ORDER BY dtPublicacao desc');
    array_unshift($result, array(0,NULL)); 

    # Campos para o formulario
    $objeto->set_campos(array(
        array ('linha' => 1,
               'nome' => 'idPlano',
               'label' => 'Plano de Cargos:',
               'tipo' => 'combo',
               'array' => $result,
               'required' => TRUE,
               'autofocus' => TRUE,
               'size' => 20),    
         array ('linha' => 1,
                'nome' => 'nivel',
               'label' => 'Nível:',
               'tipo' => 'combo',
               'array' => array(NULL,"Doutorado","Superior","Médio","Fundamental","Elementar"),
               'required' => TRUE,
               'size' => 20),
        array ('linha' => 1,
               'nome' => 'idTipoCargo',
               'label' => 'Cargo:',
               'tipo' => 'combo',
               'array' => $cargo,
               'required' => TRUE,
               'size' => 20),    
        array ('linha' => 1,
               'nome' => 'faixa',
               'label' => 'Faixa:',
               'tipo' => 'texto',
	       'required' => TRUE,
               'size' => 20),    
         array ('linha' => 1,
               'nome' => 'valor',
               'label' => 'Valor:',
               'tipo' => 'moeda',
	       'required' => TRUE,
               'size' => 10)));

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);
    
    # Verifica se veio do pcv e muda volta do formulário
    if(!is_null($pcv)){
        $objeto->set_voltarForm('cadastroPlanoCargos.php?fase=tabela&id='.$pcv);
        $objeto->set_linkListar('cadastroPlanoCargos.php?fase=tabela&id='.$pcv);
        $objeto->set_linkGravar('?fase=gravar&pcv='.$pcv);
    }

    ################################################################
    switch ($fase) {
        case "" :
        case "listar" :
            
            $form = new Form('?fase=listar');
            
            $controle = new Input('parametroPlano','combo','Plano:',1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Nivel');
            $controle->set_array($result);
            $controle->set_valor($parametroPlano);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(5);
            $form->add_item($controle);
            
            $controle = new Input('parametroNivel','combo','Nivel:',1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Nivel');
            $controle->set_array(array(NULL,"Doutorado","Superior","Médio","Fundamental","Elementar"));
            $controle->set_valor($parametroNivel);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(5);
            $form->add_item($controle);
            
            $form->show();
            $objeto->listar();
            
            break;

        case "editar" :	
        case "excluir" :	
        case "gravar" :
            $objeto->$fase($id);
            break;
    }									 	 		

    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}