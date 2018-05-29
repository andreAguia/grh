<?php
/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */

# Servidor logado 
$idUsuario = NULL;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso){    
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();
    
    # Pega o ano exercicio quando vem da área de férias
    $anoBase = get("parametroAnoExercicio",date('Y'));
    
    # Pega a lotação quando vem da área de férias
    $lotacaoArea = get("lotacaoArea");
    
    # Transforma em nulo a máscara *
    if($lotacaoArea == "*"){
        $lotacaoArea = NULL;
    }
    
    # Monta o select
    $select2 = "SELECT tbservidor.idFuncional,
                            tbpessoa.nome,
                            concat(IFNULL(tblotacao.UADM,''),' - ',IFNULL(tblotacao.DIR,''),' - ',IFNULL(tblotacao.GER,'')) lotacao,
                            tbservidor.idServidor,
                            tbservidor.dtAdmissao,
                            sum(numDias) as soma
                       FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                                     LEFT JOIN tbferias USING (idServidor)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                         JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao) 
                     WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                       ";
        
        # Verifica se tem filtro por lotação
        if(!is_null($lotacaoArea)){  // senão verifica o da classe
            $select2 .= ' AND (tblotacao.idlotacao = "'.$lotacaoArea.'")';
        }
        
        $select2 .= "
              AND anoExercicio = $anoBase
        GROUP BY tbpessoa.nome
         ORDER BY tblotacao.uadm, tblotacao.dir, tblotacao.ger"; 
            
    # Pega os dados do banco
    $result = $servidor->select($select2,TRUE);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Resumo Anual de Férias Solicitadas e Fruídas');
    $relatorio->set_tituloLinha2("Exercício: ".$anoBase);
    if(is_null($lotacaoArea)){
        $relatorio->set_subtitulo('Agrupados por Lotação');
    }
    $relatorio->set_label(array("Id Funcional","Nome","Lotação","Cargo","Admissão","Dias de Férias"));
    $relatorio->set_funcao(array(NULL,NULL,NULL,NULL,"date_to_php"));
    $relatorio->set_align(array("center","left",NULL,"left","left"));
    $relatorio->set_classe(array(NULL,NULL,NULL,"pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,NULL,"get_cargo"));
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(2);
    $relatorio->set_dataImpressao(FALSE);
    $relatorio->show();

    $page->terminaPagina();
}
