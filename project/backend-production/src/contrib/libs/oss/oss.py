from .api import *
from .xml_handler import *


class OSS():
    def __init__(self, host, key_id, key_secret):
        host = 'oss-cn-beijing-internal.aliyuncs.com'
        key_id = 'uLmwkyi2tLw0pj7L'
        key_secret = 'DnNH0hXvDV2zqlf9HaCNrNpLwOBXIb'
        self.__oss = OssAPI(host, key_id, key_secret)
        self.__prefix = '0/'

    def bucket(self, bucket):
        self.__bucket = bucket
        return self

    def set_prefix(self, prefix):
        self.__prefix = prefix
        return self

    def upload(self, filename, location, split=True):
        target = filename
        if split:
            target = '{0}/{1}'.format(filename[0:2], filename)
        if self.__prefix:
            target = '{0}/{1}'.format(self.__prefix.rstrip('/'), target)
        # content_type = 'image/jpeg'
        # headers = {'Content-Disposition': 'inline'}
        # res = self.__oss.put_object_from_file(self.__bucket, target, location, content_type, headers)
        res = self.__oss.put_object_from_file(self.__bucket, target, location)
        if res.status == 200:
            return target


def _test_case():
    host = 'oss-cn-beijing-internal.aliyuncs.com'
    key_id = 'uLmwkyi2tLw0pj7L'
    key_secret = 'DnNH0hXvDV2zqlf9HaCNrNpLwOBXIb'
    oss = OSS(host, key_id, key_secret).bucket('hm-dev').set_prefix('0/')

    filename = '7d0d47426613a071a386c76a5f8239da.jpg'
    location = '/dev/shm/{0}'.format(filename)
    target = oss.upload(filename, location)
    print(target)


if __name__ == '__main__':
    _test_case()


# vim:ts=4:sw=4
