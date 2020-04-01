<?php
/**
 * Área de Licença Prêmio
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
    $fase = get('fase');
    
    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh',FALSE);
    if($grh){
        # Grava no log a atividade
        $atividade = "Visualizou a área de formação";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
    }
    
    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    # Pega os parâmetros    
    $parametroNivel = post('parametroNivel',get_session('parametroNivel','Todos'));
    $parametroEscolaridade = post('parametroEscolaridade',get_session('parametroEscolaridade','*'));
    $parametroCurso = post('parametroCurso',get_session('parametroCurso'));
    $parametroInstituicao = post('parametroInstituicao',get_session('parametroInstituicao'));
    
    # Joga os parâmetros par as sessions   
    set_session('parametroNivel',$parametroNivel);
    set_session('parametroEscolaridade',$parametroEscolaridade);
    set_session('parametroCurso',$parametroCurso);
    set_session('parametroInstituicao',$parametroInstituicao);
    
    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();
    
    # Cabeçalho da Página
    if($fase <> "relatorio"){
        AreaServidor::cabecalho();
    }
            
################################################################
    
    switch ($fase){
        case "" : 
            br(4);
            aguarde();
            br();
            
            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
                p("Aguarde...","center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=exibeLista');
            break;
        
################################################################
        
        case "exibeLista" :
            $grid = new Grid();
            $grid->abreColuna(12);
            br();

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar","grh.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar,"left");
            
            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dessa pesquisa");
            $botaoRel->set_url("?fase=relatorio");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            $menu1->add_link($botaoRel,"right");
            
            $menu1->show();
            
        ##############
            
            # Pega os dados da combo escolaridade
            $result = $pessoal->select('SELECT idEscolaridade, 
                                               escolaridade
                                          FROM tbescolaridade
                                      ORDER BY idEscolaridade');
            array_unshift($result, array("*","Todos")); # Adiciona o valor de nulo
            
            # Formulário de Pesquisa
            $form = new Form('?');
            
            # Nivel do Cargo    
            $controle = new Input('parametroNivel','combo','Nível do Cargo Efetivo:',1);
            $controle->set_size(20);
            $controle->set_title('Nível do Cargo');
            $controle->set_valor($parametroNivel);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $controle->set_array(array("Todos","Doutorado","Superior","Médio","Fundamental","Elementar"));
            $controle->set_autofocus(TRUE);
            $form->add_item($controle);
                    
            # Escolaridade do Servidor    
            $controle = new Input('parametroEscolaridade','combo','Formação:',1);
            $controle->set_size(20);
            $controle->set_title('Escolaridade do Servidor');
            $controle->set_valor($parametroEscolaridade);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $controle->set_array($result);
            $form->add_item($controle);
            
            # Curso
            $controle = new Input('parametroCurso','texto','Curso:',1);
            $controle->set_size(100);
            $controle->set_title('Curso');
            $controle->set_valor($parametroCurso);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);
            
            # Curso
            $controle = new Input('parametroInstituicao','texto','Instituição:',1);
            $controle->set_size(100);
            $controle->set_title('Instituiçlão de Ensino');
            $controle->set_valor($parametroInstituicao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);

            $form->show();

        ##############
           
            # Pega os dados
            $select ='SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      tbservidor.idServidor,
                      tbescolaridade.escolaridade,
                      idFormacao,
                      instEnsino
                 FROM tbformacao JOIN tbpessoa USING (idPessoa)
                                 JOIN tbservidor USING (idPessoa)
                                 JOIN tbescolaridade USING (idEscolaridade)
                                 LEFT JOIN tbcargo USING (idCargo)
                                 LEFT JOIN tbtipocargo USING (idTipoCargo)
                 WHERE situacao = 1
                   AND idPerfil = 1';
            
            if($parametroNivel <> "Todos"){
                $select .= ' AND tbtipocargo.nivel = "'.$parametroNivel.'"';
            }
            
            if($parametroEscolaridade <> "*"){
                $select .= ' AND tbformacao.idEscolaridade = '.$parametroEscolaridade;
            }
            
            if(!vazio($parametroCurso)){
                $select .= ' AND tbformacao.habilitacao LIKE "%'.$parametroCurso.'%"';
            }
            
            if(!vazio($parametroInstituicao)){
                $select .= ' AND tbformacao.instEnsino LIKE "%'.$parametroInstituicao.'%"';
            }
                  
            $select .= ' ORDER BY tbpessoa.nome, tbformacao.anoTerm';
            
            #echo $select;

            $result = $pessoal->select($select);

            $tabela = new Tabela();   
            $tabela->set_titulo('Cadastro de Formação Servidores');
            #$tabela->set_subtitulo('Filtro: '.$relatorioParametro);
            $tabela->set_label(array("IdFuncional","Nome","Cargo","Lotação","Escolaridade","Curso","Instituição"));
            $tabela->set_conteudo($result);
            $tabela->set_align(array("center","left","left","left","center","left","left"));
            $tabela->set_classe(array(NULL,NULL,"pessoal","pessoal",NULL,"Formacao"));
            $tabela->set_metodo(array(NULL,NULL,"get_Cargo","get_Lotacao",NULL,"get_curso"));
            
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editaServidor');
            $tabela->show();
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
    ################################################################
        
        case "editaServidor" :
            br(8);
            aguarde();
            
            # Informa o $id Servidor
            set_session('idServidorPesquisado',$id);
            
            # Informa a origem
            set_session('origem','areaFormacao');
            
            # Carrega a página específica
            loadPage('servidorFormacao.php');
            break; 
        
    ################################################################
        
        # Relatório
        case "relatorio" :
                
                $subTitulo = NULL;
                
                # Pega os dados
                $select ='SELECT tbservidor.idfuncional,
                          tbpessoa.nome,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbescolaridade.escolaridade,
                          idFormacao,
                          instEnsino
                     FROM tbformacao JOIN tbpessoa USING (idPessoa)
                                     JOIN tbservidor USING (idPessoa)
                                     JOIN tbescolaridade USING (idEscolaridade)
                                     LEFT JOIN tbcargo USING (idCargo)
                                     LEFT JOIN tbtipocargo USING (idTipoCargo)
                     WHERE situacao = 1
                       AND idPerfil = 1';

                if($parametroNivel <> "Todos"){
                    $select .= ' AND tbtipocargo.nivel = "'.$parametroNivel.'"';
                    $subTitulo .= 'Cargo Efetivo de Nível '.$parametroNivel.'<br/>';
                }

                if($parametroEscolaridade <> "*"){
                    $select .= ' AND tbformacao.idEscolaridade = '.$parametroEscolaridade;
                    $subTitulo .= 'Curso de Nível '.$pessoal->get_escolaridade($parametroEscolaridade).'<br/>';
                }

                if(!vazio($parametroCurso)){
                    $select .= ' AND tbformacao.habilitacao like "%'.$parametroCurso.'%"';
                    $subTitulo .= 'Filtro Curso: '.$parametroCurso.'<br/>';
                }
                
                if(!vazio($parametroInstituicao)){
                    $select .= ' AND tbformacao.instEnsino LIKE "%'.$parametroInstituicao.'%"';
                    $subTitulo .= 'Filtro Instituição: '.$parametroInstituicao.'<br/>';
                }

                $select .= ' ORDER BY tbpessoa.nome, tbformacao.anoTerm';
                
                # Monta o Relatório
                $relatorio = new Relatorio();
                $relatorio->set_titulo('Relatório Geral de Formação Servidores');
                
                if(!is_null($subTitulo)){
                    $relatorio->set_subtitulo($subTitulo);
                }
                
                $result = $pessoal->select($select);
                
                $relatorio->set_label(array("IdFuncional","Nome","Cargo","Lotação","Escolaridade","Curso","Instituição"));
                $relatorio->set_conteudo($result);
                $relatorio->set_align(array("center","left","left","left","center","left","left"));
                $relatorio->set_classe(array(NULL,NULL,"pessoal","pessoal",NULL,"Formacao"));
                $relatorio->set_metodo(array(NULL,NULL,"get_Cargo","get_Lotacao",NULL,"get_curso"));
                $relatorio->show();
                break;
                
    }
    
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}


