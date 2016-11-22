<?php
/**
 * Histórico de Férias de um servidor
 *  
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null;	# Servidor Editado na pesquisa do sistema do GRH

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso)
{    
    # Conecta ao Banco de Dados   
    $pessoal = new Pessoal();
    $intra = new Intra();
	
    # Verifica a fase do programa
    $fase = get('fase');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
            
    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();
    
    # Cabeçalho da Página
    AreaServidor::cabecalho();
    
    botaoVoltar('servidorMenu.php');
    
    # Exibe os dados do Servidor
    Grh::listaDadosServidor($idServidorPesquisado);
    
    # Limita a tela
    $grid = new Grid();
    $grid->abreColuna(12);
    
    titulo("Exclusão de Servidor");
    
    switch ($fase)
    {
        case "":
            $callout = new Callout("alert");
            $callout->abre();
            p('ATENÇÃO',"center");
            p('Esteja certo que o desejado é excluir um servidor e não exonerá-lo ou demití-lo.','center');
            p('A exclusão de um servidor do sistema apaga todos os seus dados.','center');
            p('Uma vez excluídos, os dados do servidor não poderão ser recuperados.','center');
            $callout->fecha();
            
            $grid = new Grid("center");
            $grid->abreColuna(4);
            
            $callout = new Callout("secondary","center");
            $callout->abre();
            p('Deseja realmente excluir esse servidor?');
            
            # Cria um menu
            $menu1 = new MenuBar();

            # Sim Excluir
            $linkBotao3 = new Link("Sim, desejo excluir","?fase=exclusao");
            $linkBotao3->set_class('button alert');        
            $linkBotao3->set_title('Excluir o servidor');
            $linkBotao3->set_accessKey('S');
            $menu1->add_link($linkBotao3,"left");

            # Não Excluir
            $linkBotao1 = new Link("Não","servidorMenu.php");
            $linkBotao1->set_class('button');
            $linkBotao1->set_title('Não desejo excluir');
            $linkBotao1->set_accessKey('N');
            $menu1->add_link($linkBotao1,"right");

            $menu1->show();
            $callout->fecha();
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
        case "exclusao":
            $idServidor = $idServidorPesquisado;
            $idPessoa = $pessoal->get_idPessoa($idServidor);
            
            br(4);
            aguarde();            
            br();
            p("Excluindo ...","center");
            
            # Dados para o log
            $data = date("Y-m-d H:i:s");           
            
            #####################################################
            # tbservidor
            #####################################################
            $nomeTabela = "tbservidor";
            $idCampo = "idServidor";
            $nomeServidor = $pessoal->get_nome($idServidor);
            $atividade = "Exclusão de todos os dados do Servidor: ".$nomeServidor;
            
            # Dados da tabela
            $pessoal->set_tabela($nomeTabela);
            $pessoal->set_idCampo($idCampo);
            
            # Apaga
            $pessoal->excluir($idServidor);
            
            # Log
            $intra->registraLog($idUsuario,$data,$atividade,$nomeTabela,$idServidor,3,$idServidor);
            
            #####################################################
            # Tabelas vinculadas pelo idservidor
            #####################################################
            $tabelas = array("tbatestado","tbaverbacao","tbcedido","tbcomissao","tbdiaria","tbelogio","tbfaltas","tbferias","tbfolga","tbgratificacao","tbhistcessao","tbhistlot","tblicenca","tbprogressao","tbpublicacaoPremio","tbsuspensao","tbtrabalhoTre","tbtrienio");
            $idCampo = array("idAtestado","idAverbacao","idCedido","idComissao","idDiaria","idElogio","idFaltas","idFerias","idFolga","idGratificacao","idHistCessao","idhistlot","idLicenca","idProgressao","idPublicacaoPremio","idSuspensao","idTrabalhoTre","idTrienio");
            
            # Apaga os dados das tabelas
            $numTabelas = count($tabelas);
            
            for ($item = 0; $item < $numTabelas; $item++) {
                $atividade = "Exclusão dos dados do Servidor: $nomeServidor da tabela $tabelas[$item]";            
            
                # Verifica se tem dados desse servidor
                $select = "SELECT $idCampo[$item]
                             FROM $tabelas[$item]
                            WHERE idServidor = $idServidor";		

                $result = $pessoal->select($select);
                $totalRegistros = count($result);

                # Dados da tabela
                $pessoal->set_tabela($tabelas[$item]);
                $pessoal->set_idCampo($idCampo[$item]);

                # Percorre apagando cada id
                if($totalRegistros > 0){
                    foreach ($result as $linha){
                        # Apaga
                        $pessoal->excluir($linha[0]);

                        # Log
                        $intra->registraLog($idUsuario,$data,$atividade,$tabelas[$item],$linha[0],3,$idServidor);
                    }
                }            
            }
            
            #####################################################
            # Tabelas vinculadas pelo idpessoa
            #####################################################
            $tabelas = array("tbpessoa","tbdependente","tbdocumentacao","tbformacao");
            $idCampo = array("idpessoa","idDependente","iddocumentacao","idformacao");
            
            # Apaga os dados das tabelas
            $numTabelas = count($tabelas);
            
            for ($item = 0; $item < $numTabelas; $item++) {
                $atividade = "Exclusão dos dados do Servidor: $nomeServidor da tabela $tabelas[$item]";            
            
                # Verifica se tem dados desse servidor
                $select = "SELECT $idCampo[$item]
                             FROM $tabelas[$item]
                            WHERE idPessoa = $idPessoa";		

                $result = $pessoal->select($select);
                $totalRegistros = count($result);

                # Dados da tabela
                $pessoal->set_tabela($tabelas[$item]);
                $pessoal->set_idCampo($idCampo[$item]);

                # Percorre apagando cada id
                if($totalRegistros > 0){
                    foreach ($result as $linha){
                        # Apaga
                        $pessoal->excluir($linha[0]);

                        # Log
                        $intra->registraLog($idUsuario,$data,$atividade,$tabelas[$item],$linha[0],3,$idServidor);
                    }
                }            
            }
                   
            ########################################################
            loadPage("servidor.php");
            break;
    }
    $grid->fechaColuna();
    $grid->fechaGrid();
            
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}