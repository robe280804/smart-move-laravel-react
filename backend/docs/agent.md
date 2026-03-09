# Fitness Agent

## Design exercise categories

1. 🏋️ **Weight training**
2. 🏃 **Sprint training**
3. 💪 **Strength training**
4. 🔥 **Conditioning / HIIT**
5. 🧘 **Mobility / warmups**

/_ Example _/
{
"id": "uuid",
"name": "",
"**category**": "",
"primary_muscles": [],
"secondary_muscles": [],
"equipment": "",
"movement_pattern": "",
"training_goal": [],
"difficulty": "",
"energy_system": "",
"description": ""
}

## Category

strength
hypertrophy
sprint
plyometric
conditioning
cardio
mobility
core
agility

## Collection info

{
"status": "green",
"optimizer_status": "ok",
"indexed_vectors_count": 0,
"points_count": 0,
"segments_count": 2,
"config": {
"params": {
"vectors": {
"debse": {
"size": 768,
"distance": "Cosine",
"hnsw_config": {
"m": 24,
"ef_construct": 256,
"payload_m": 24
},
"on_disk": false,
"datatype": "float32"
}
},
"shard_number": 1,
"replication_factor": 1,
"write_consistency_factor": 1,
"on_disk_payload": true,
"sparse_vectors": {
"sparse": {
"index": {
"on_disk": true
}
}
}
},
"hnsw_config": {
"m": 16,
"ef_construct": 100,
"full_scan_threshold": 10000,
"max_indexing_threads": 0,
"on_disk": false
},
"optimizer_config": {
"deleted_threshold": 0.2,
"vacuum_min_vector_number": 1000,
"default_segment_number": 0,
"max_segment_size": null,
"memmap_threshold": null,
"indexing_threshold": 10000,
"flush_interval_sec": 5,
"max_optimization_threads": null,
"prevent_unoptimized": null
},
"wal_config": {
"wal_capacity_mb": 32,
"wal_segments_ahead": 0,
"wal_retain_closed": 1
},
"quantization_config": null,
"strict_mode_config": {
"enabled": true,
"unindexed_filtering_retrieve": false,
"unindexed_filtering_update": false,
"max_payload_index_count": 100
}
},
"payload_schema": {
"equipment": {
"data_type": "keyword",
"params": {
"type": "keyword"
},
"points": 0
},
"primary-muscle": {
"data_type": "keyword",
"params": {
"type": "keyword"
},
"points": 0
},
"category": {
"data_type": "keyword",
"params": {
"type": "keyword"
},
"points": 0
},
"difficulty": {
"data_type": "keyword",
"params": {
"type": "keyword"
},
"points": 0
},
"energy_sistem": {
"data_type": "keyword",
"params": {
"type": "keyword"
},
"points": 0
},
"secondary_muscle": {
"data_type": "keyword",
"params": {
"type": "keyword"
},
"points": 0
}
},
"update_queue": {
"length": 0
}
}
