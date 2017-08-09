# vim: set fileencoding=utf-8 :

from . import db
from mongoengine.base.datastructures import BaseList
from bson.dbref import DBRef


class DocumentExtend(db.Document):

    meta = {
        'abstract': True,
        'strict': False,
        'auto_create_index': False
    }

    def visible(self):
        raise NotImplementedError

    def _filter(self, use_db_field=False, fields=None, exclude=None):
        if fields is None:
            fields = self.visible()
        if exclude is not None:
            fields = list(set(fields) - set(exclude))
        return super(DocumentExtend, self).to_mongo(use_db_field=use_db_field, fields=fields)

    def to_bson(self, *args, **kwargs):
        '''
        kwargs: use_db_field, fields
        '''
        use_db_field = kwargs.get('use_db_field', False)
        fields = kwargs.get('fields', None)
        if fields is not None:
            fields = [str(fields)] if not isinstance(fields, list) else fields
            fields += args
        exclude = kwargs.get('exclude', None)
        if exclude is not None:
            exclude = [str(exclude)] if not isinstance(exclude, list) else exclude
        data = self._filter(use_db_field=use_db_field, fields=fields, exclude=exclude)
        for ref in args:
            parts = ref.split('.', 1)
            part_0 = parts[0]
            if part_0 in self:
                value = getattr(self, part_0)
                if len(parts) == 2:
                    part_1 = parts[1]
                    value = getattr(value, part_1)
                if isinstance(value, BaseList):
                    if len(parts) == 2:
                        temp = data.get(part_0)
                        temp.update({part_1: [item._filter() for item in value]})
                    else:
                        data.update({part_0: [item._filter() for item in value]})
                elif isinstance(value, DBRef):
                    data.update({part_0: None})
                else:
                    data.update({part_0: value._filter()})
        return data


# vim:ts=4:sw=4
