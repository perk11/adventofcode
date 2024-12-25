package main

import (
	"fmt"
	"os"
	"strings"
)

type Key struct {
	pins []int
}
type Lock struct {
	heights []int
}

var keys []Key
var locks []Lock

func main() {
	filenames := []string{"testInput", "input"}
	for _, fileName := range filenames {
		fileContents, err := os.ReadFile(fileName)
		if err != nil {
			panic(err)
		}
		fmt.Println(fileName)
		keys = make([]Key, 0)
		locks = make([]Lock, 0)
		lines := strings.Split(string(fileContents), "\n")
		for lineIndex := 0; lineIndex+7 <= len(lines); lineIndex += 8 {
			parseInputEntry(lines[lineIndex : lineIndex+7])
		}

		total := 0
		for _, key := range keys {
			for _, lock := range locks {
				if doesKeyFitLock(key, lock) {
					total++
				}
			}
		}

		println(total)
	}
}
func doesKeyFitLock(key Key, lock Lock) bool {
	for pinIndex, pin := range key.pins {
		if lock.heights[pinIndex]+pin > 5 {
			return false
		}
	}
	return true
}
func parseInputEntry(lines []string) {
	if string(lines[0][0]) == "#" {
		lock := Lock{}
		lock.heights = make([]int, 5)
		for column := 0; column < 5; column++ {
			for height := 6; height >= 0; height-- {
				if string(lines[height][column]) == "#" {
					lock.heights[column] = height
					break
				}
			}
		}
		locks = append(locks, lock)
	} else {
		key := Key{}
		key.pins = make([]int, 5)
		for column := 0; column < 5; column++ {
			for height := 5; height >= 0; height-- {
				if string(lines[6-height][column]) == "#" {
					key.pins[column] = height
					break
				}
			}
		}
		keys = append(keys, key)
	}
}
