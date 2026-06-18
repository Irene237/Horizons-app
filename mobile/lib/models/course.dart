class Course {
  final int id;
  final String title;
  final String description;
  final String startDate;
  final int availablePlaces;

  Course({
    required this.id,
    required this.title,
    required this.description,
    required this.startDate,
    required this.availablePlaces,
  });

  factory Course.fromJson(Map<String, dynamic> json) {
    return Course(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      title: json['title'] ?? 'Sans titre',
      description: json['description'] ?? '',
      startDate: json['start_date'] ?? '',
      // S'assure que le calcul dynamique de Laravel est bien lu comme un entier
      availablePlaces: json['available_places'] is int 
          ? json['available_places'] 
          : int.tryParse(json['available_places'].toString()) ?? 0,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'description': description,
      'start_date': startDate,
      'available_places': availablePlaces,
    };
  }
}