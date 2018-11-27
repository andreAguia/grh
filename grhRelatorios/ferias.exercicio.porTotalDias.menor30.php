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
    
    # Pega o ano exercicio
    $parametroAno = get("parametroAno",date('Y'));
    
    # Pega a lotação
    $parametroLotacao = get("parametroLotacao");
    
    # Transforma em nulo a máscara *
    if($parametroLotacao == "*"){
        $parametroLotacao = NULL;
    }
    
    ######

    $select2 = "SELECT tbservidor.idFuncional,
                           tbpessoa.nome,
                           tbservidor.idServidor,
                           tbservidor.idServidor,
                           tbservidor.dtAdmissao,
                           '-',
                           tbsituacao.situacao
                      FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                         JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao) 
                     WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND YEAR(tbservidor.dtAdmissao) < $parametroAno
                      ";
                 
    if(!is_null($parametroLotacao)){ 
        $select2 .= ' AND (tblotacao.idlotacao = "'.$parametroLotacao.'")';
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
              AND anoExercicio = $parametroAno";

    if(!is_null($parametroLotacao)){
        $select2 .= ' AND (tblotacao.idlotacao = "'.$parametroLotacao.'")';
    }

    $select2 .= "
            AND tbservidor.situacao = 1
       ORDER BY tbpessoa.nome asc)
          ORDER BY tbpessoa.nome asc";  
    
    $result = $servidor->select($select2);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Férias de Servidores Com Menos de 30 Dias');
    $relatorio->set_tituloLinha2('Ano Exercício: '.$parametroAno);
    
    if(!is_null($parametroLotacao)){
        $relatorio->set_tituloLinha3($servidor->get_nomeLotacao($parametroLotacao));
    }
    
    $relatorio->set_subtitulo('Agrupados pelo Total de Dias e Ordenado pelo Nome');
    $relatorio->set_subtitulo("== Não Solicitaram ==");

    $relatorio->set_label(array("Id","Servidor","Lotação","Perfil","Admissão","Dias","Situação"));
    $relatorio->set_align(array("center","left","left"));
    $relatorio->set_funcao(array(NULL,NULL,NULL,NULL,"date_to_php"));
    $relatorio->set_classe(array(NULL,NULL,"pessoal","pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,"get_lotacaoSimples","get_perfilSimples"));
    $relatorio->set_conteudo($result);
    
    
    $relatorio->set_dataImpressao(FALSE);
    $relatorio->show();
    
    #####
    
    $select1 = "(SELECT tbservidor.idFuncional,
                        tbpessoa.nome,
                        tbservidor.idServidor,
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
                   AND anoExercicio = $parametroAno";

    # Verifica se tem filtro por lotação
    if(!is_null($parametroLotacao)){  // senão verifica o da classe
        $select1 .= ' AND (tblotacao.idlotacao = "'.$parametroLotacao.'")';
    }

    $select1 .= " GROUP BY tbpessoa.nome
                  HAVING sum(numDias) < 30)
                  ORDER BY soma,tbpessoa.nome";          

    $result = $servidor->select($select1);

    $relatorio = new Relatorio();
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);

    $relatorio->set_label(array("Id","Servidor","Lotação","Perfil","Admissão","Dias","Situação"));
    $relatorio->set_align(array("center","left","left"));
    $relatorio->set_funcao(array(NULL,NULL,NULL,NULL,"date_to_php"));
    $relatorio->set_classe(array(NULL,NULL,"pessoal","pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,"get_lotacaoSimples","get_perfilSimples"));
    $relatorio->set_numGrupo(5);
    $relatorio->set_conteudo($result);
    
    $relatorio->set_dataImpressao(FALSE);
    $relatorio->show();

    $page->terminaPagina();
}
