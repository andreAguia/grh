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
    # primeiro select: servidores ativos que não tiraram ferias
    $select2 = "(SELECT tbservidor.idFuncional,
                       tbpessoa.nome,
                       concat(IFNULL(tblotacao.UADM,''),' - ',IFNULL(tblotacao.DIR,''),' - ',IFNULL(tblotacao.GER,'')) lotacao,
                       tbservidor.idServidor,
                       tbservidor.dtAdmissao,
                       '-' as soma,
                       tbsituacao.situacao
                  FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                                     JOIN tbhistlot USING (idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                     JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao) 
                 WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                 AND YEAR(tbservidor.dtAdmissao) < $anoBase
                  ";

    # Verifica se tem filtro por lotação
    if(!is_null($lotacaoArea)){  // senão verifica o da classe
        $select2 .= ' AND (tblotacao.idlotacao = "'.$lotacaoArea.'")';
    }

    $select2 .= "
         AND tbservidor.situacao = 1
         AND tbpessoa.nome NOT IN 
         (SELECT tbpessoa.nome
         FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                            JOIN tbferias USING (idservidor)
                            JOIN tbhistlot USING (idServidor)
                            JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
              AND anoExercicio = $anoBase";

    if(!is_null($lotacaoArea)){
        $select2 .= ' AND (tblotacao.idlotacao = "'.$lotacaoArea.'")';
    }

    # segundo select: servidores ativos e inativos tiraram ferias inferior a 30 dias
    $select2 .= "
            AND tbservidor.situacao = 1
       ORDER BY tbpessoa.nome asc)
          ORDER BY tblotacao.uadm, tblotacao.dir, tblotacao.ger)
        UNION
        (SELECT tbservidor.idFuncional,
                            tbpessoa.nome,
                            concat(IFNULL(tblotacao.UADM,''),' - ',IFNULL(tblotacao.DIR,''),' - ',IFNULL(tblotacao.GER,'')) lotacao,
                            tbservidor.idServidor,
                            tbservidor.dtAdmissao,
                            sum(numDias) as soma,
                            tbsituacao.situacao
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
        HAVING soma < 30
         ORDER BY tblotacao.uadm, tblotacao.dir, tblotacao.ger)
         ORDER BY 3,2
        "; 
    
    # Pega os dados do banco
    $result = $servidor->select($select2,TRUE);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Servidores com Férias Pendentes');
    $relatorio->set_tituloLinha2("Exercício: ".$anoBase);
    if(!is_null($lotacaoArea)){
        $relatorio->set_tituloLinha3($servidor->get_nomeLotacao($lotacaoArea));
    }
    $relatorio->set_subtitulo('Agrupados por Lotação');
    $relatorio->set_label(array("Id Funcional","Nome","Lotação","Cargo","Admissão","Dias de Férias","Situação"));
    $relatorio->set_funcao(array(NULL,NULL,NULL,NULL,"date_to_php"));
    $relatorio->set_align(array("center","left","left","left","center"));
    $relatorio->set_classe(array(NULL,NULL,NULL,"pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,NULL,"get_cargo"));
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(2);
    $relatorio->set_dataImpressao(FALSE);
    $relatorio->show();

    $page->terminaPagina();
}
