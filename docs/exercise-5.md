# Exercise 5 — Migrations no Yii2

Branch: `exercise-5`

## Objetivos

- Reduzir consultas ao banco e custo de hidratação na listagem de clientes e suas pessoas relacionadas.
- Adicionar cache com invalidação automática e fragment cache por seção, mantendo consistência.

## Checklist

- [x] Fase 1
  - [x] Migration de índices + teste functional.
  - [x] Paginação/filtro (repo/use case/controller/view) + testes unit/functional.
  - [x] Seed grande + functional.
- [x] Fase 2
  - [x] Consultas menores e menos hidratação (read-model).
  - [x] Testes unit (Read-model asArray + DTOs).
- [x] Fase 3
  - [x] Interface/adapter de cache + use case (FileCache) + unit/functional.
  - [x] Behaviors de invalidação (Customer/Person/Pivot) + functional.
  - [x] Fragment cache por seção (bloco de cliente) + functional.
- [ ] Fase 4 (opcional)
  - [ ] Trocar provider para MemCache (compose + config) + smoke.
  - [ ] Observabilidade/documentação + validação final.

## Justificativas

- Fase 1 — índices, paginação e seed:
  - Índices reduzem custo de joins e aceleram buscas por colunas usadas em filtros/joins, diminuindo I/O e latência nas consultas agregadas.
  - Paginação evita trazer muitos registros para memória, reduzindo tempo de resposta e uso de CPU/heap no servidor web.
  - O seed grande é importante para validar comportamento em volume real e para medir ganhos antes/depois das otimizações.

- Fase 2 — read-model e menor hidratação:
  - Consultas que retornam apenas colunas necessárias (asArray/join minimal) evitam a criação de muitos ActiveRecord, reduzindo overhead de hidratação e aceleração do pipeline de dados.
  - O read-model (DTOs) torna a camada de apresentação desacoplada do ORM, permitindo consultas otimizadas sem comprometer regras de domínio.
  - Testes unit garantem que o mapeamento linhas → DTOs está correto e que cálculos (ex.: idade) permanecem determinísticos.

- Fase 3 — cache, invalidação e fragment cache:
  - Abstrair o cache via interface permite trocar providers (FileCache → Memcached/Redis) sem alterar lógica de aplicação.
  - Invalidação por tags (TagDependency) fornece granularidade: só os blocos impactados (customer_x / link_customer_x / person_x) são recarregados, preservando outros caches e evitando busts desnecessários.
  - Fragment cache por seção reduz custo de renderização (views) mantendo consistência com as tags e as behaviors que disparam invalidações automaticamente; testes funcionais asseguram correção do mecanismo.

- Fase 4 — trocas de provider e observabilidade

> Não foi implementada por falta de tempo e por não estar no escopo do exercício. Deixei documentado para uma melhoria futura. Contudo, estou pronto para implementar, caso deseje.

  - Migrar para Memcached/Redis reduz latência e permite cache distribuído para múltiplos nós; exige ajustes de configuração e testes de smoke.
  - Observabilidade (logs, métricas de cache hit/miss, dashboards) e documentação operacional são necessárias antes de promover a mudança para produção, para monitorar impacto e planejar rollback se necessário.